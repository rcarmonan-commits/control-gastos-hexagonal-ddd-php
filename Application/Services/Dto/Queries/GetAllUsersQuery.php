<?php

declare(strict_types=1);

final class GetAllUsersQuery
{
    private ?string $search;
    private int $page;
    private int $limit;

    public function __construct(?string $search = null, int $page = 1, int $limit = 50)
    {
        $trimmed = $search !== null ? trim($search) : '';
        $this->search = $trimmed !== '' ? $trimmed : null;
        $this->page = $page > 0 ? $page : 1;
        $this->limit = $limit > 0 ? $limit : 50;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
