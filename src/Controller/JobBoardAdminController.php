<?php

namespace Drupal\job_board\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class JobBoardAdminController extends ControllerBase
{

    protected $entityTypeManager;

    public function __construct(EntityTypeManagerInterface $entity_type_manager)
    {
        $this->entityTypeManager = $entity_type_manager;
    }

    public static function create(ContainerInterface $container)
    {
        return new static($container->get('entity_type.manager'));
    }

    public function dashboard()
    {
        $storage = $this->entityTypeManager->getStorage('job_application');
        $query = $storage->getQuery()->accessCheck(FALSE)->sort('id', 'DESC');
        $aids = $query->execute();

        $applications = $storage->loadMultiple($aids);
        $rows = [];
        $file_storage = $this->entityTypeManager->getStorage('file');

        foreach ($applications as $app) {
            /** @var \Drupal\job_board\JobApplicationInterface $app */
            $cv_url = '#';
            if ($app->getCvId()) {
                $cv_file = $file_storage->load($app->getCvId());
                /** @var \Drupal\file\FileInterface $cv_file */
                if ($cv_file)
                    $cv_url = $cv_file->createFileUrl(FALSE);
            }

            $cl_url = '#';
            if ($app->getCoverLetterId()) {
                $cl_file = $file_storage->load($app->getCoverLetterId());
                /** @var \Drupal\file\FileInterface $cl_file */
                if ($cl_file)
                    $cl_url = $cl_file->createFileUrl(FALSE);
            }

            $rows[] = [
                'id' => $app->id(),
                'name' => $app->getApplicantName(),
                'email' => $app->get('email')->value,
                'phone' => $app->getPhone(),
                'start_date' => $app->getStartDate(),
                'cv_url' => $cv_url,
                'cl_url' => $cl_url,
                'status' => $app->getStatus(),
            ];
        }

        return [
            '#theme' => 'job_board_admin_dashboard',
            '#applications' => $rows,
            '#attached' => ['library' => ['job_board/job_board_assets']],
        ];
    }
}
