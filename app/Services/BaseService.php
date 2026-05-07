<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

abstract class BaseService
{
    abstract protected function query(): Builder;

    public function getAll(int $perPage = 10)
    {
        return $this->query()
            ->latest()
            ->paginate($perPage);
    }
}
