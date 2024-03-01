<?php

namespace Core\Exceptions;

use Exception;

class TooManyHitsException extends Exception
{
    public $message = 'Maximum of Hits Reached!';
}
