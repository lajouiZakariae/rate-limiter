<?php

namespace RateLimiter\Interfaces;

interface IStorage
{
    /**
     * @param string $key
     * @param mixed $value
     * 
     * @return void
     * 
     */
    public function set(string $key, mixed $value): void;

    /**
     * @param string $key
     * 
     * @return mixed
     * @throws \RateLimiter\Exceptions\InvalideRateLimiterException
     */
    public function get(string $key): mixed;

    /**
     * @param string $key
     * 
     * @return bool
     * 
     */
    public function exists(string $key): bool;

    /**
     * @param string $key
     * 
     * @return bool
     * 
     */
    public function missing(string $key): bool;


    /**
     * @param string $key
     * 
     * @return void
     * @throws \RateLimiter\Exceptions\InvalideRateLimiterExceptions
     */
    public function delete(string $key): void;
}
