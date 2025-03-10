<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Exceptions\NotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

/**
 * Abstract Repository providing common database operations.
 *
 * @template TModel of Model
 */
abstract class AbstractRepository
{
    /**
     * @param TModel $model
     */
    public function __construct(protected Model $model)
    {
    }

    /**
     * Dynamically handle method calls to the model.
     *
     * @param string $method
     * @param array<int|string, mixed> $attributes
     * @return mixed
     */
    public function __call(string $method, array $attributes): mixed
    {
        return $this->model->$method(...$attributes);
    }

    /**
     * Get the repository model instance.
     *
     * @return TModel
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Insert multiple records into the database.
     *
     * @param array<int, array<string, mixed>> $attributes
     * @return bool
     */
    public function createMany(array $attributes): bool
    {
        return $this->model->insert($attributes);
    }

    /**
     * Find a record by its primary key.
     *
     * @param int|string $id
     * @param array<int, string> $columns
     * @return TModel
     * @throws NotFoundException
     */
    public function find(int|string $id, array $columns = ['*']): Model
    {
        $record = $this->model->find($id, $columns);

        if (! $record) {
            throw new NotFoundException('Not Found', Response::HTTP_NOT_FOUND);
        }

        return $record;
    }

    /**
     * Find the first record matching attributes or throw an exception.
     *
     * @param array<string, mixed> $attributes
     * @param array<int, string> $columns
     * @return TModel
     * @throws NotFoundException
     */
    public function firstOrFailByColumn(array $attributes, array $columns = ['*']): Model
    {
        $record = $this->model->where($attributes)->first($columns);

        if (! $record) {
            throw new NotFoundException("Record not found", Response::HTTP_NOT_FOUND);
        }

        return $record;
    }

    /**
     * Delete a record matching the given attributes.
     *
     * @param array<string, mixed> $attributes
     * @return bool
     * @throws NotFoundException
     */
    public function delete(array $attributes): bool
    {
        $record = $this->firstOrFailByColumn($attributes);

        return $record->delete();
    }

    /**
     * Increment a column value for a specific record.
     *
     * @param int $id
     * @param string $column
     * @param int $amount
     * @param array<string, mixed> $extra
     * @return int
     */
    public function increment(int $id, string $column, int $amount = 1, array $extra = []): int
    {
        $record = $this->find($id);

        return $record->increment($column, $amount, $extra);
    }

    /**
     * Decrement a column value for a specific record.
     *
     * @param int $id
     * @param string $column
     * @param int $amount
     * @param array<string, mixed> $extra
     * @return int
     */
    public function decrement(int $id, string $column, int $amount = 1, array $extra = []): int
    {
        $record = $this->find($id);

        return $record->decrement($column, $amount, $extra);
    }

    /**
     * Retrieve all records.
     *
     * @return Collection<int, TModel>
     */
    public function all(): Collection
    {
        return $this->model->query()->get();
    }

    /**
     * Save the given model instance.
     *
     * @param TModel $model
     * @return bool
     */
    public function save(Model $model): bool
    {
        return $model->save();
    }

    /**
     * Find the first record matching attributes or create a new one.
     *
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $values
     * @return TModel
     */
    public function firstOrCreate(array $attributes, array $values): Model
    {
        return $this->model->firstOrCreate($attributes, $values);
    }

    /**
     * Update a record by its primary key.
     *
     * @param int $id
     * @param array<string, mixed> $values
     * @param string $primaryKey
     * @return int
     * @throws NotFoundException
     */
    public function updateById(int $id, array $values, string $primaryKey = 'id'): int
    {
        $record = $this->model->where($primaryKey, $id)->first();

        if (! $record) {
            throw new NotFoundException("Record not found", Response::HTTP_NOT_FOUND);
        }

        return $record->update($values) ? 1 : 0;
    }
}
