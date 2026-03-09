<?php

namespace Drupal\job_board\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;

class JobBoardService
{

    protected $entityTypeManager;
    protected $loggerFactory;
    protected $mailManager;

    public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_factory, MailManagerInterface $mail_manager)
    {
        $this->entityTypeManager = $entity_type_manager;
        $this->loggerFactory = $logger_factory;
        $this->mailManager = $mail_manager;
    }

    public function processApplication(array $values)
    {
        try {
            $storage = $this->entityTypeManager->getStorage('job_application');

            $cv_fid = !empty($values['cv']) ? reset($values['cv']) : null;
            $cover_letter_fid = !empty($values['cover_letter']) ? reset($values['cover_letter']) : null;

            $application = $storage->create([
                'applicant_name' => $values['applicant_name'],
                'email' => $values['email'],
                'phone' => $values['phone'],
                'cv' => $cv_fid,
                'cover_letter' => $cover_letter_fid,
                'start_date' => $values['start_date'],
            ]);

            $status = $application->save();
            if ($status !== SAVED_NEW && $status !== SAVED_UPDATED) {
                throw new \Exception("Database failed to save the application entity.");
            }

            if ($cv_fid) {
                $cv_file = $this->entityTypeManager->getStorage('file')->load($cv_fid);
                /** @var \Drupal\file\FileInterface $cv_file */
                if ($cv_file) {
                    $cv_file->setPermanent();
                    $cv_file->save();
                } else {
                    throw new \Exception("Could not load CV file entity.");
                }
            }
            if ($cover_letter_fid) {
                $cl_file = $this->entityTypeManager->getStorage('file')->load($cover_letter_fid);
                /** @var \Drupal\file\FileInterface $cl_file */
                if ($cl_file) {
                    $cl_file->setPermanent();
                    $cl_file->save();
                } else {
                    throw new \Exception("Could not load Cover Letter file entity.");
                }
            }

            $this->loggerFactory->get('job_board')->info('New application saved successfully for @name', ['@name' => $values['applicant_name']]);
            $this->sendNotificationEmail($application);

            return TRUE;
        } catch (\Exception $e) {
            $this->loggerFactory->get('job_board')->error('Error processing application: @message', ['@message' => $e->getMessage()]);
            throw $e;
        }
    }

    protected function sendNotificationEmail($application)
    {
        try {
            $applicant_email = $application->get('email')->value;

            if (empty($applicant_email))
                return;

            $module = 'job_board';
            $key = 'application_received';
            $to = $applicant_email;
            $langcode = \Drupal::currentUser()->getPreferredLangcode();
            $params = ['application' => $application];
            $send = TRUE;

            $result = $this->mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

            if ($result['result'] !== TRUE) {
                $this->loggerFactory->get('job_board')->error('Probleme d\'envoi vers le candidat.');
            } else {
                $this->loggerFactory->get('job_board')->notice('Email envoye a @email pour @name', [
                    '@email' => $to,
                    '@name' => $application->get('applicant_name')->value,
                ]);
            }
        } catch (\Exception $e) {
            $this->loggerFactory->get('job_board')->error('Exception email: @message', ['@message' => $e->getMessage()]);
        }
    }

    public function processAction($id, $mail_key, $action_name)
    {
        $storage = $this->entityTypeManager->getStorage('job_application');
        /** @var \Drupal\job_board\JobApplicationInterface $application */
        $application = $storage->load($id);

        if (!$application) {
            return null; // Signals application not found
        }

        $module = 'job_board';
        $to = $application->get('email')->value;
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $params = ['application' => $application];
        $send = TRUE;

        if ($to) {
            $result = $this->mailManager->mail($module, $mail_key, $to, $langcode, $params, NULL, $send);
            if ($result['result'] === TRUE) {
                // Save the new status to the database so the UI can hide the buttons
                $application->setStatus(strtolower($action_name));
                $application->save();

                $this->loggerFactory->get('job_board')->info('Application @id was @action.', ['@id' => $id, '@action' => strtolower($action_name)]);
                return true;
            }
        }

        $this->loggerFactory->get('job_board')->error('Failed to send email for application @id.', ['@id' => $id]);
        return false;
    }
}
