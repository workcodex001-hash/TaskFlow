<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Encapsulation sécurisée de la requête HTTP
 */
class Request
{
    private array $getParams;
    private array $postParams;
    private array $server;

    public function __construct()
    {
        $this->getParams = filter_input_array(INPUT_GET, FILTER_DEFAULT) ?? [];
        $this->postParams = filter_input_array(INPUT_POST, FILTER_DEFAULT) ?? [];
        $this->server = $_SERVER;
    }

    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function getUri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        // Retirer les paramètres de requête de l'URL
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        return '/' . trim($uri, '/');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getParams[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->postParams[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->getParams, $this->postParams);
    }

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function getCsrfToken(): ?string
    {
        return $this->post('csrf_token') ?? $this->server['HTTP_X_CSRF_TOKEN'] ?? null;
    }

    public function getClientIp(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR'] ?? $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
