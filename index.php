<?php

use Core\RateLimiter;

require "vendor/autoload.php";

$rateLimiter = new RateLimiter("storage/cache");

$rateLimiter->limit('user', 5);

try {
    $rateLimiter->hit('user');
} catch (\Throwable $th) {
    dump($th->getMessage());
}

if ($rateLimiter->tooManyAttempts('user')) {
    echo 'yeeee';
    $rateLimiter->clear('user');
}
