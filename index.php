<?php

use Core\RateLimiter;

require "vendor/autoload.php";

$rateLimiter = new RateLimiter("app/cache");

$rateLimiter->limit('user', 5);

$rateLimiter->hit('user');
