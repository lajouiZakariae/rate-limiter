<?php

namespace RateLimiter;

use Exception;
use RateLimiter\Exceptions\InvalideRateLimiterException;
use RateLimiter\Exceptions\TooManyHitsException;
use RateLimiter\Interfaces\IStorage;
use stdClass;

class RateLimiter
{
    private IStorage $storage;

    public function __construct(array $storageSettings)
    {
        if (!isset($storageSettings['storage'])) throw new Exception('The Storage Type is Required');

        if ($storageSettings['storage'] === 'file') $this->storage = new FileStorage($storageSettings['path']);
    }

    /**
     * Register a key with a limit per minute
     *
     * @param string $key
     * @param int $perMinute
     * 
     * @return void
     */
    public function limit(string $key, int $timesPerPeriod, int $period = 60): void
    {
        $hashedKey = md5($key);

        if ($this->storage->missing($hashedKey)) {
            $fileContent = 0 . '|' . $timesPerPeriod . '|' . time() . '|' . time() + $period . '|' . $period;

            $this->storage->set($hashedKey, $fileContent);
        }
    }

    public function limitHourly(string $key, int $perHour): void
    {
        $this->limit($key, $perHour, 3600);
    }

    /**
     * Increments the Rate
     *
     * @param string $key
     * 
     * @return void
     * @throws TooManyHitsException|InvalideRateLimiterException
     */
    public function hit(string $key, int $times = 0): void
    {
        $hashedKey = md5($key);

        $this->checkKeyExistance($hashedKey);

        $props = $this->decodeKeyContent($this->storage->get($hashedKey));

        if ($props->endTime < time()) {
            $props->numberOfHits = 0;
            $props->startedTime = time();
            $props->endTime = time() + (int)$props->period;
        };

        $props->numberOfHits = $props->numberOfHits + $times;

        if ($props->numberOfHits > $props->maximum) throw new TooManyHitsException();

        $fileContent = $this->encodeKeyContent($props);

        $this->storage->set($hashedKey, $fileContent);
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

        $this->checkKeyExistance($hashedKey);

        $props = $this->decodeKeyContent($this->storage->get($hashedKey));

        if ($props->endTime <= time()) {
            $props->numberOfHits = 0;
            $props->startedTime = time();
            $props->endTime = time() + (int)$props->period;

            $this->storage->set($hashedKey, $this->encodeKeyContent($props));
        }

        return $props->numberOfHits === $props->maximum;
    }

    public function clear(string $key): void
    {
        $hashedKey = md5($key);

        $this->checkKeyExistance($hashedKey);

        $props = $this->decodeKeyContent($this->storage->get($hashedKey));

        $props->numberOfHits = 0;
        $props->startedTime = time();
        $props->endTime = time() + $props->period;

        $this->storage->set($hashedKey, $this->encodeKeyContent($props));
    }

    public function destroy(string $key): void
    {
        $hashedKey = md5($key);

        $this->checkKeyExistance($hashedKey);

        $this->storage->delete($hashedKey);
    }

    /**
     * 
     * @param string $keyFilePath
     * 
     * @return void
     * 
     */
    private function checkKeyExistance(string $hashedKey): void
    {
        if ($this->storage->missing($hashedKey)) throw new InvalideRateLimiterException();
    }

    /**
     * Convert the properties to a string
     *
     * @param object $props
     * 
     * @return string
     * 
     */
    private function encodeKeyContent(object $props): string
    {
        $string = $props->numberOfHits .
            '|' . $props->maximum .
            '|' . $props->startedTime .
            '|' . $props->endTime .
            '|' . $props->period;

        return $string;
    }

    /**
     * Extract the properties from the string
     *
     * @param string $string
     * 
     * @return object
     * 
     */
    private function decodeKeyContent(string $string): object
    {
        [$numberOfHits, $maximum, $startedTime, $endTime, $period] = array_map(
            fn ($paramAsString) => intval($paramAsString),
            explode('|', $string)
        );

        $props = new stdClass();
        $props->numberOfHits = $numberOfHits;
        $props->maximum = $maximum;
        $props->startedTime = $startedTime;
        $props->endTime = $endTime;
        $props->period = $period;

        return $props;
    }
}
