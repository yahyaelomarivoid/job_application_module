<?php

namespace Drupal\job_board\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\job_board\Service\JobBoardService;

class JobBoardActionController extends ControllerBase
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

    public function accept($id)
    {
        return $this->handleAction($id, 'application_accepted', 'Accepted');
    }

    public function reject($id)
    {
        return $this->handleAction($id, 'application_rejected', 'Rejected');
    }

    protected function handleAction($id, $mail_key, $action_name)
    {
        $result = $this->jobBoardService->processAction($id, $mail_key, $action_name);

        if ($result === null) {
            $this->messenger()->addError($this->t('Application not found.'));
        } elseif ($result === true) {
            $this->messenger()->addStatus($this->t('Application @id has been @action, and an email was sent.', [
                '@id' => $id,
                '@action' => strtolower($action_name),
            ]));
        } else {
            $this->messenger()->addError($this->t('Application was @action but the email failed to send.', [
                '@action' => strtolower($action_name)
            ]));
        }

        return new RedirectResponse(Url::fromRoute('job_board.admin_dashboard')->toString());
    }
}
