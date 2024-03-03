<?php

use Core\RateLimiter;

require "vendor/autoload.php";

$rateLimiter = new RateLimiter("storage/cache");

$rateLimiter->limit('user', 5);

if ($rateLimiter->tooManyAttempts('user')) {
    echo 'yeeee';
}

try {
    $rateLimiter->hit('user');
} catch (\Throwable $th) {
    dump($th->getMessage());
}
