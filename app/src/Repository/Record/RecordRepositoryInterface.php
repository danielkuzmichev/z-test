<?php

namespace App\Repository\Record;

use App\Entity\Record;

interface RecordRepositoryInterface
{
    public function getById(string $id): Record;

    /** @return Record[] */
    public function getAll(array $criteria, int $page = 1, int $limit = 10): array;
}