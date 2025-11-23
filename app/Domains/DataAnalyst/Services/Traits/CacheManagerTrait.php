<?php
// app/Domains/DataAnalyst/Services/Traits/CacheManagerTrait.php

namespace App\Domains\DataAnalyst\Services\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheManagerTrait
{
    protected $cacheTTL = 300;

    /**
     * Ejecuta consulta con cache
     */
    protected function executeCachedQuery(string $query, string $cacheKey, int $ttl = null): array
    {
        $ttl = $ttl ?? $this->cacheTTL;

        return Cache::remember($cacheKey, $ttl, function () use ($query) {
            $queryJobConfig = $this->bigQuery->query($query);
            $queryResults = $this->bigQuery->runQuery($queryJobConfig);
            
            $results = [];
            foreach ($queryResults as $row) {
                $results[] = $row;
            }
            
            return $results;
        });
    }

    /**
     * Genera clave de cache única basada en filtros
     */
    protected function generateCacheKey(string $prefix, array $filters): string
    {
        return $prefix . '_' . md5(serialize($filters));
    }
}