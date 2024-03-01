<?php

namespace Core\Exceptions;

use Exception;

class InvalideRateLimiterException extends Exception
{
    public $message = "Invalid Rate Limiter Key";
}
