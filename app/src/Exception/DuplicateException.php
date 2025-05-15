<?php

namespace App\Exception;

class DuplicateException extends DomainException
{
    public function __construct(
        $message = 'Duplicate',
        $code = 422,
    ) {
        parent::__construct($message, $code);
    }
}