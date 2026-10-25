<?php

declare(strict_types=1);

require_once __DIR__ . '/../Ports/In/GetAllUsersUseCase.php';
require_once __DIR__ . '/../Ports/Out/GetAllUsersPort.php';
require_once __DIR__ . '/../Ports/Out/CachePort.php';

final class GetAllUsersService implements GetAllUsersUseCase
{
    private GetAllUsersPort $getAllUsersPort;
    private ?CachePort $cachePort;

    public function __construct(GetAllUsersPort $getAllUsersPort, ?CachePort $cachePort = null)
    {
        $this->getAllUsersPort = $getAllUsersPort;
        $this->cachePort = $cachePort;
    }

    public function execute(GetAllUsersQuery $query): array
    {
        $cacheKey = 'users:list:' . md5(
            ($query->getSearch() ?? '') . ':' . $query->getPage() . ':' . $query->getLimit()
        );

        if ($this->cachePort !== null) {
            $cached = $this->cachePort->get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $all = $this->getAllUsersPort->getAll($query->getSearch());
        $offset = ($query->getPage() - 1) * $query->getLimit();
        $sliced = array_slice($all, $offset, $query->getLimit());

        if ($this->cachePort !== null) {
            $this->cachePort->set($cacheKey, $sliced, 60);
        }

        return $sliced;
    }
}
