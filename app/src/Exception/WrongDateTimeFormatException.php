<?php

namespace App\Exception;

class WrongDateTimeFormatException extends DomainException
{
    public function __construct(
        $message = 'Wrong date-time format',
        $code = 400,
    ) {
        parent::__construct($message, $code);
    }
}
