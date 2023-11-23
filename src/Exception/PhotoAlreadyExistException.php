<?php

namespace App\Exception;

use Exception;
use Throwable;

class PhotoAlreadyExistException extends Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}