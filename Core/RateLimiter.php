<?php

namespace Core;

use Exception;

class RateLimiter
{
    public function __construct(private string $storage_path)
    {
        if (!is_dir($storage_path)) mkdir($storage_path);
    }

    public function limit(string $key, int $perMinute)
    {
        $hashedKey = md5($key);

        $keyFilePath = $this->storage_path . '/' . $hashedKey;

        if (!is_file($keyFilePath)) {
            $fileContent = 0 . '|' . $perMinute . '|' . time() . '|' . time() + 60;

            file_put_contents($keyFilePath, $fileContent);
        }
    }

    public function hit(string $key): void
    {
        $hashedKey = md5($key);
        $keyFilePath = $this->storage_path . '/' . $hashedKey;

        if (!is_file($keyFilePath)) throw new Exception("Invalid Rate Limiter Key");

        [$numberOfHits, $maximum, $startedTime, $endTime] = array_map(fn ($paramAsString) => intval($paramAsString), explode('|', file_get_contents($keyFilePath)));

        if ($endTime < time()) {
            $numberOfHits = 0;
            $startedTime = time();
            $endTime = time() + 60;
        };

        if ($numberOfHits >= $maximum) throw new Exception('Maximum of Hits Reached!');

        $numberOfHits++;

        $fileContent = $numberOfHits . '|' . $maximum . '|' . $startedTime . '|' . $endTime;

        file_put_contents($keyFilePath, $fileContent);
    }
}
