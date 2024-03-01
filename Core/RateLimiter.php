<?php

namespace Core;

use Core\Exceptions\InvalideRateLimiterException;
use Core\Exceptions\TooManyHitsException;
use Exception;

class RateLimiter
{
    public function __construct(private string $storage_path)
    {
        if (!is_dir($storage_path)) mkdir($storage_path);
    }

    /**
     * Register a rate limiter with a limit per minute
     *
     * @param string $key
     * @param int $perMinute
     * 
     * @return void
     */
    public function limit(string $key, int $perMinute): void
    {
        $hashedKey = md5($key);

        $keyFilePath = $this->storage_path . '/' . $hashedKey;

        if (!is_file($keyFilePath)) {
            $fileContent = 0 . '|' . $perMinute . '|' . time() . '|' . time() + 60;

            file_put_contents($keyFilePath, $fileContent);
        }
    }

    /**
     * Increments the Rate
     *
     * @param string $key
     * 
     * @return void
     * @throws TooManyHitsException|InvalideRateLimiterException
     */
    public function hit(string $key): void
    {
        $hashedKey = md5($key);
        $keyFilePath = $this->storage_path . '/' . $hashedKey;

        if (!is_file($keyFilePath)) throw new InvalideRateLimiterException();

        [$numberOfHits, $maximum, $startedTime, $endTime] = array_map(fn ($paramAsString) => intval($paramAsString), explode('|', file_get_contents($keyFilePath)));

        if ($endTime < time()) {
            $numberOfHits = 0;
            $startedTime = time();
            $endTime = time() + 60;
        };

        if ($numberOfHits >= $maximum) throw new TooManyHitsException();

        $numberOfHits++;

        $fileContent = $numberOfHits . '|' . $maximum . '|' . $startedTime . '|' . $endTime;

        file_put_contents($keyFilePath, $fileContent);
    }
}
