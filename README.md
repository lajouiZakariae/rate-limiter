# Rate Limiter Built With PhP

## Introduction

This package is a rate limiter that allows you to define a limit per (minute,hour, or any number of seconds)
It Provides different methods that allow you to define, delete, increment the rate.

## Basic Usage

```php
use RateLimiter\RateLimiter;

$rateLimiter = new RateLimiter([
    'storage' => "file",
    'path' => "storage/cache",
]);

$rateLimiter->limitHourly('user:one', 3);

if ($rateLimiter->tooManyAttempts('user:one')) {
    # Do Something
}

$rateLimiter->hit('user:one');
```

## Create a Rate Limiter

You can create a rete limiter by instanciating the RateLimiter class, the only required parameter is an associative array.

You need to define wich type of storage you will be using, currently this package only supports the file system storage.

If you choose to save the rate limiter's data in the file system you need to provide a path key that shows the rate limiter where to store the data.

If the directory does not exist it will create it for you

Note: The path should be an absolute path to the directory where you want the data to be saved.
