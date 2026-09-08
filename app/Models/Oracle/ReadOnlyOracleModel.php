<?php

namespace App\Models\Oracle;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

abstract class ReadOnlyOracleModel extends Model
{
    protected $connection = 'oracle_legacy';
    public $timestamps = false;

    public function save(array $options = []): bool
    {
        throw new RuntimeException('Mutation Guard: Legacy Oracle 11g models are strictly Read-Only.');
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        throw new RuntimeException('Mutation Guard: Legacy Oracle 11g models are strictly Read-Only.');
    }

    public function delete(): ?bool
    {
        throw new RuntimeException('Mutation Guard: Legacy Oracle 11g models are strictly Read-Only.');
    }
}
