<?php

namespace Core;

use Core\Exceptions\InvalideRateLimiterException;
use Core\Exceptions\TooManyHitsException;
use stdClass;

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

        $this->checkKeyExistance($keyFilePath);

        $props = $this->decodeKeyContent(file_get_contents($keyFilePath));

        if ($props->endTime < time()) {
            $props->numberOfHits = 0;
            $props->startedTime = time();
            $props->endTime = time() + 60;
        };

        if ($props->numberOfHits >= $props->maximum) throw new TooManyHitsException();

        $props->numberOfHits++;

        $fileContent = $this->encodeKeyContent($props);

        file_put_contents($keyFilePath, $fileContent);
    }

    private function encodeKeyContent(object $props): string
    {
        $string = $props->numberOfHits .
            '|' . $props->maximum .
            '|' . $props->startedTime .
            '|' . $props->endTime;
        return $string;
    }

    private function decodeKeyContent(string $string): object
    {
        [$numberOfHits, $maximum, $startedTime, $endTime] = array_map(
            fn ($paramAsString) => intval($paramAsString),
            explode('|', $string)
        );

        $props = new stdClass();
        $props->numberOfHits = $numberOfHits;
        $props->maximum = $maximum;
        $props->startedTime = $startedTime;
        $props->endTime = $endTime;

        return $props;
    }

    /**
     * Wheter the rate limiter reached the limit or not
     *
     * @param string $key
     * 
     * @return bool
     * 
     */
    public function tooManyAttempts(string $key): bool
    {
        $hashedKey = md5($key);

        $keyFilePath = $this->storage_path . '/' . $hashedKey;

        $this->checkKeyExistance($keyFilePath);

        $props = $this->decodeKeyContent(file_get_contents($keyFilePath));

        return $props->numberOfHits === $props->maximum;
    }

    private function checkKeyExistance(string $keyFilePath): void
    {
        if (!is_file($keyFilePath)) throw new InvalideRateLimiterException();
    }
}
