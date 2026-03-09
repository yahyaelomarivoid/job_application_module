<?php

namespace Drupal\job_board\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * @Block(
 *   id = "job_application_block",
 *   admin_label = @Translation("Job Application Form Block"),
 *   category = @Translation("Job Board")
 * )
 */
class JobApplicationBlock extends BlockBase
{

    public function build()
    {
        return ['form' => \Drupal::formBuilder()->getForm('\Drupal\job_board\Form\JobApplicationForm')];
    }

    protected function blockAccess(AccountInterface $account)
    {
        return AccessResult::allowedIfHasPermission($account, 'submit job application');
    }
}
