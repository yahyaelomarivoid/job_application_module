<?php

namespace Drupal\job_board\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Job Board settings for this site.
 */
class JobBoardSettingsForm extends ConfigFormBase
{

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'job_board_settings';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return ['job_board.settings'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('job_board.settings');

        $form['hr_email'] = [
            '#type' => 'email',
            '#title' => $this->t('HR Department Email'),
            '#description' => $this->t('New job applications will be sent to this email address.'),
            '#default_value' => $config->get('hr_email') ?? \Drupal::config('system.site')->get('mail'),
            '#required' => TRUE,
        ];

        $form['max_cover_length'] = [
            '#type' => 'number',
            '#title' => $this->t('Maximum Cover Letter Length'),
            '#description' => $this->t('Maximum number of characters allowed in the cover letter.'),
            '#default_value' => $config->get('max_cover_length') ?? 2000,
            '#min' => 100,
        ];

        $form['urgently_hiring'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Show Urgently Hiring banner on application block?'),
            '#default_value' => $config->get('urgently_hiring') ?? FALSE,
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $email = $form_state->getValue('hr_email');
        if (!\Drupal::service('email.validator')->isValid($email)) {
            $form_state->setErrorByName('hr_email', $this->t('The HR email address is not valid.'));
        }

        $max_length = $form_state->getValue('max_cover_length');
        if (!is_numeric($max_length) || $max_length < 100 || $max_length > 10000) {
            $form_state->setErrorByName('max_cover_length', $this->t('The maximum cover letter length must be between 100 and 10000 characters.'));
        }

        parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->config('job_board.settings')
            ->set('hr_email', $form_state->getValue('hr_email'))
            ->set('max_cover_length', $form_state->getValue('max_cover_length'))
            ->set('urgently_hiring', $form_state->getValue('urgently_hiring'))
            ->save();

        parent::submitForm($form, $form_state);
    }

}
