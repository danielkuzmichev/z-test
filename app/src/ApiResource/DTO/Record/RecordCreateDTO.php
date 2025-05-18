<?php

namespace App\ApiResource\DTO\Record;

use Symfony\Component\Validator\Constraints as Assert;

class RecordCreateDTO
{
    public function __construct(
        #[Assert\Positive]
        public int $code,
        #[Assert\NotBlank]
        public string $number,
        #[Assert\NotBlank]
        public string $status,
        #[Assert\NotBlank]
        public string $title,
    ) {
    }
}
