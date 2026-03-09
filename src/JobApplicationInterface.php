<?php

namespace Drupal\job_board;

use Drupal\Core\Entity\ContentEntityInterface;

interface JobApplicationInterface extends ContentEntityInterface
{
    public function getApplicantName(): string;
    public function setApplicantName(string $name): self;
    public function getStartDate(): ?string;
    public function setStartDate(string $date): self;
    public function getPhone(): ?string;
    public function setPhone(string $phone): self;
    public function getCvId(): ?int;
    public function setCvId(int $fid): self;
    public function getCoverLetterId(): ?int;
    public function setCoverLetterId(int $fid): self;
    public function getStatus(): string;
    public function setStatus(string $status): self;
}
