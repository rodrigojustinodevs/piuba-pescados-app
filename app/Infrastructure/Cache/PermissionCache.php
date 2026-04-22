<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use Illuminate\Cache\Repository as CacheRepository;

final readonly class PermissionCache
{
    private const int TTL_SECONDS = 3600; // 1 hora
    private const string PREFIX      = 'permissions';

    public function __construct(
        private CacheRepository $cache,
    ) {
    }

    /**
     * @param callable(): list<string> $callback
     * @return list<string>
     */
    public function remember(string $userId, string $companyId, callable $callback): array
    {
        return $this->cache->remember(
            key: $this->key($userId, $companyId),
            ttl: self::TTL_SECONDS,
            callback: fn (): array => $callback(),
        );
    }

    public function flush(string $userId, string $companyId): void
    {
        $this->cache->forget($this->key($userId, $companyId));
    }

    public function flushAllForUser(string $userId): void
    {
        $this->cache->tags([$this->userTag($userId)])->flush();
    }

    private function key(string $userId, string $companyId): string
    {
        return sprintf('%s:user:%s:company:%s', self::PREFIX, $userId, $companyId);
    }

    private function userTag(string $userId): string
    {
        return sprintf('%s:user:%s', self::PREFIX, $userId);
    }
}
