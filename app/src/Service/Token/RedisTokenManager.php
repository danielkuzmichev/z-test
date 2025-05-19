<?php

namespace App\Service\Token;

class RedisTokenManager
{
    private string $prefix = 'auth_token:';
    private int $ttl = 36000;

    public function __construct(private \Predis\Client $redis)
    {
    }

    public function generateToken(int $userId): string
    {
        /** Для проверки оставим предсказуемый */
        // $token = bin2hex(random_bytes(32));
        $token = $userId;
        $this->redis->setex($this->prefix.$token, $this->ttl, $userId);

        return $token;
    }

    public function getUserIdByToken(string $token): ?int
    {
        $userId = $this->redis->get($this->prefix.$token);

        return false !== $userId ? (int) $userId : null;
    }

    public function invalidateToken(string $token): void
    {
        $this->redis->del($this->prefix.$token);
    }
}
