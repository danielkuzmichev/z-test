<?php

namespace App\Exception;

class NotFoundException extends DomainException
{
    public function __construct(
        $message = 'Not found',
        $code = 404,
    ) {
        parent::__construct($message, $code);
    }
}
