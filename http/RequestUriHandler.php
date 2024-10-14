<?php

namespace Peach\Http;

class RequestUriHandler
{
    private string $method;
    private array $handlers;

    public function __construct(string $method)
    {
        $this->method = strtolower($method);
    }

    public function handle(string $path, callable $handler): void
    {
        $this->handlers[$path] = $handler;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getHandler(string $uri): false|callable
    {
        if (array_key_exists($uri, $this->handlers)) return $this->handlers[$uri];
        else return false;
    }
}