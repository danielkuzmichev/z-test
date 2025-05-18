<?php

namespace App\ApiResource\DTO\Record;

use Symfony\Component\Validator\Constraints as Assert;

class RecordResponseDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public int $id,
        #[Assert\NotBlank]
        public int $code,
        #[Assert\NotBlank]
        public string $number,
        #[Assert\NotBlank]
        public string $status,
        #[Assert\NotBlank]
        public string $title,
        #[Assert\NotBlank]
        #[Assert\DateTime()]
        public \DateTimeInterface $changeAt
    ) {}
}
