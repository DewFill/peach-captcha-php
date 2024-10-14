<?php

namespace Peach\Repositories;

class RequestRepository
{
    private string $method;
    public function __construct(string $method, private string $path, private string $query = "", private string $body = "")
    {
        $this->method = strtolower($method);
    }

    function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }
}