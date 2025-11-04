<?php

namespace App\Exceptions;

use Exception;

class CompteException extends Exception
{
    public function __construct(string $message = 'Erreur liée au compte', int $code = 400, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}