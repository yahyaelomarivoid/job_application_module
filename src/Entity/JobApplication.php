<?php

namespace Drupal\job_board\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\job_board\JobApplicationInterface;

/**
 * @ContentEntityType(
 *   id = "job_application",
 *   label = @Translation("Job Application"),
 *   base_table = "job_application",
 *   entity_keys = {"id" = "id", "uuid" = "uuid", "label" = "applicant_name"},
 *   links = {"canonical" = "/job_application/{job_application}"}
 * )
 */
class JobApplication extends ContentEntityBase implements JobApplicationInterface
{



    public function getApplicantName(): string
    {
        return $this->get('applicant_name')->value ?? '';
    }
    public function setApplicantName(string $name): self
    {
        $this->set('applicant_name', $name);
        return $this;
    }

    public function getStartDate(): ?string
    {
        return $this->get('start_date')->value;
    }
    public function setStartDate(string $date): self
    {
        $this->set('start_date', $date);
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->get('phone')->value ?? null;
    }
    public function setPhone(string $phone): self
    {
        $this->set('phone', $phone);
        return $this;
    }

    public function getCvId(): ?int
    {
        return $this->get('cv')->target_id ?? null;
    }
    public function setCvId(int $fid): self
    {
        $this->set('cv', $fid);
        return $this;
    }

    public function getCoverLetterId(): ?int
    {
        return $this->get('cover_letter')->target_id ?? null;
    }
    public function setCoverLetterId(int $fid): self
    {
        $this->set('cover_letter', $fid);
        return $this;
    }

    public function getStatus(): string
    {
        return $this->get('status')->value ?? 'pending';
    }

    public function setStatus(string $status): self
    {
        $this->set('status', $status);
        return $this;
    }

    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);

        $fields['applicant_name'] = BaseFieldDefinition::create('string')
            ->setLabel(new TranslatableMarkup('Name'))
            ->setRequired(TRUE);

        $fields['email'] = BaseFieldDefinition::create('email')
            ->setLabel(new TranslatableMarkup('Email'))
            ->setRequired(TRUE);

        $fields['phone'] = BaseFieldDefinition::create('string')
            ->setLabel(new TranslatableMarkup('Phone'))
            ->setRequired(TRUE);

        $fields['cv'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(new TranslatableMarkup('CV Upload'))
            ->setSetting('target_type', 'file')
            ->setRequired(TRUE);

        $fields['cover_letter'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(new TranslatableMarkup('Cover Letter Upload'))
            ->setSetting('target_type', 'file')
            ->setRequired(TRUE);

        $fields['start_date'] = BaseFieldDefinition::create('datetime')
            ->setLabel(new TranslatableMarkup('Start Date'))
            ->setRequired(TRUE)
            ->setSetting('datetime_type', 'date');

        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(new TranslatableMarkup('Created'));

        $fields['status'] = BaseFieldDefinition::create('string')
            ->setLabel(new TranslatableMarkup('Status'))
            ->setDefaultValue('pending');

        return $fields;
    }
}
