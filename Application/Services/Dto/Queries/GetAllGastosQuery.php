<?php

declare(strict_types=1);

final class GetAllGastosQuery
{
    private ?string $search;
    private ?string $nombreUsuario;
    private int $page;
    private int $limit;

    public function __construct(
        ?string $search = null,
        ?string $nombreUsuario = null,
        int $page = 1,
        int $limit = 50
    ) {
        $trimmedSearch = $search !== null ? trim($search) : '';
        $trimmedUser = $nombreUsuario !== null ? trim($nombreUsuario) : '';

        $this->search = $trimmedSearch !== '' ? $trimmedSearch : null;
        $this->nombreUsuario = $trimmedUser !== '' ? $trimmedUser : null;
        $this->page = $page > 0 ? $page : 1;
        $this->limit = $limit > 0 ? $limit : 50;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function getNombreUsuario(): ?string
    {
        return $this->nombreUsuario;
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
