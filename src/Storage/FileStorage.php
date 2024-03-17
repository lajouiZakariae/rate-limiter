<?php

namespace RateLimiter\Storage;

use Exception;
use RateLimiter\Exceptions\InvalideRateLimiterException;
use RateLimiter\Interfaces\IStorage;

class FileStorage implements IStorage
{

    public function __construct(private string $storagePath)
    {
        if (!is_dir($storagePath)) mkdir($storagePath, recursive: true);
    }

    /**
     * @param string $key
     * @param mixed $value
     * 
     * @return void
     * 
     */
    public function set(string $key, mixed $value): void
    {
        if (file_put_contents($this->getKeyFilePath($key), $value) === false) throw new Exception('Could not create file');
    }

    /**
     * @param string $key  
     * 
     * @return mixed
     * @throws \RateLimiter\Exceptions\InvalideRateLimiterException
     */
    public function get(string $key): mixed
    {
        if (file_exists($this->getKeyFilePath($key)) === false) throw new InvalideRateLimiterException("Key $key Not Found");

        return file_get_contents($this->getKeyFilePath($key));
    }

    /**
     * @param string $key
     * 
     * @return bool
     * 
     */
    function exists(string $key): bool
    {
        return file_exists($this->getKeyFilePath($key));
    }

    /**
     * @param string $key
     * 
     * @return bool
     * 
     */
    function missing(string $key): bool
    {
        return !$this->exists($key);
    }

    /**
     * @param string $key
     * 
     * @return void
     * @throws \RateLimiter\Exceptions\InvalideRateLimiterExceptions
     */
    public function delete(string $key): void
    {
        if (file_exists($this->getKeyFilePath($key)) === false) throw new InvalideRateLimiterException("Key $key Not Found");

        unlink($this->getKeyFilePath($key));
    }

    /**
     * Get full path of the key to store
     *
     * @param string $key
     * 
     * @return string
     * 
     */
    private function getKeyFilePath(string $key): string
    {
        return $this->storagePath . (str_ends_with($this->storagePath, '/') ? '' : '/') . $key;
    }
}
