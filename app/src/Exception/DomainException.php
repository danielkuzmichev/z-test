<?php

namespace App\Exception;

use RuntimeException;

class DomainException extends RuntimeException
{
    public function __construct(
        string $message = 'Not found',
        int $code = 400,
    ) {
        parent::__construct($message, $code);
    }
}
