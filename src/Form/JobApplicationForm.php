<?php

namespace Drupal\job_board\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\job_board\Service\JobBoardService;

class JobApplicationForm extends FormBase
{

    protected $jobBoardService;

    public function __construct(JobBoardService $job_board_service)
    {
        $this->jobBoardService = $job_board_service;
    }

    public static function create(ContainerInterface $container)
    {
        return new static($container->get('job_board.service'));
    }

    public function getFormId()
    {
        return 'job_board_application_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['start_date'] = ['#type' => 'date', '#title' => $this->t('Available Start Date'), '#required' => TRUE, '#attributes' => ['class' => ['job-board-input']]];
        $form['#attached']['library'][] = 'job_board/job_board_assets';
        $form['applicant_name'] = ['#type' => 'textfield', '#title' => $this->t('Full Name'), '#required' => TRUE, '#attributes' => ['class' => ['job-board-input']]];
        $form['email'] = ['#type' => 'email', '#title' => $this->t('Email Address'), '#required' => TRUE, '#attributes' => ['class' => ['job-board-input']]];
        $form['phone'] = ['#type' => 'tel', '#title' => $this->t('Phone Number'), '#required' => TRUE, '#attributes' => ['class' => ['job-board-input']]];
        $form['cv'] = ['#type' => 'managed_file', '#title' => $this->t('Upload CV (PDF)'), '#required' => TRUE, '#upload_validators' => ['FileExtension' => ['extensions' => 'pdf']], '#upload_location' => 'public://job_applications/cv/'];
        $form['cover_letter'] = ['#type' => 'managed_file', '#title' => $this->t('Upload Cover Letter (PDF)'), '#required' => TRUE, '#upload_validators' => ['FileExtension' => ['extensions' => 'pdf']], '#upload_location' => 'public://job_applications/cover_letters/'];
        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = ['#type' => 'submit', '#value' => $this->t('Apply Now'), '#button_type' => 'primary', '#attributes' => ['class' => ['job-board-submit']]];

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        if (!\Drupal::service('email.validator')->isValid($form_state->getValue('email'))) {
            $form_state->setErrorByName('email', $this->t('The email address is not valid.'));
        }

        $phone = $form_state->getValue('phone');
        if (!preg_match('/^(06|07)[0-9]{8}$/', $phone)) {
            $form_state->setErrorByName('phone', $this->t('The phone number must be 10 digits and start with 06 or 07.'));
        }

        $start_date = $form_state->getValue('start_date');
        $today = date('Y-m-d');
        if ($start_date < $today) {
            $form_state->setErrorByName('start_date', $this->t('The start date cannot be in the past.'));
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $values = [
            'applicant_name' => $form_state->getValue('applicant_name'),
            'email' => $form_state->getValue('email'),
            'phone' => $form_state->getValue('phone'),
            'cv' => $form_state->getValue('cv'),
            'cover_letter' => $form_state->getValue('cover_letter'),
            'start_date' => $form_state->getValue('start_date'),
        ];

        try {
            $this->jobBoardService->processApplication($values);
            $this->messenger()->addStatus($this->t('Thank you, @name. Your application has been submitted successfully!', ['@name' => $form_state->getValue('applicant_name')]));
        } catch (\Exception $e) {
            $this->logger('job_board')->error('Form submission failed: @message', ['@message' => $e->getMessage()]);
            $this->messenger()->addError($this->t('There was a problem submitting your application: @error', ['@error' => $e->getMessage()]));
        }
    }
}
