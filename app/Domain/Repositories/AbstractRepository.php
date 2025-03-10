<?php

namespace App\Domain\Repositories;

use App\Exceptions\NotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

abstract class AbstractRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function __call($method, $attributes)
    {
        return $this->model->$method(...$attributes);
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }


    /**
     * Undocumented function
     *
     * @param array $attributes
     * @return boolean
     */
    public function createMany(array $attributes): bool
    {
        return $this->model->insert($attributes);
    }

    /**
     * Undocumented function
     *
     * @param integer|string $id
     * @param array $columns
     * @return Model|null
     */
    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        $record = $this->model->find($id, $columns);

        if (!$record) {
            throw new NotFoundException('Not Found', Response::HTTP_NOT_FOUND);
        }

        return $record;
    }

    /**
     * Undocumented function
     *
     * @param array $attributes
     * @param array $columns
     * @return object
     */
    public function firstOrFailByColumn(array $attributes, array $columns = ['*']): object
    {
        $record = $this->model->where($attributes)->first($columns);

        if (!$record) {
            throw new NotFoundException("Record not found", Response::HTTP_NOT_FOUND);
        }

        return $record;
    }

    /**
     * Undocumented function
     *
     * @param array $attributes
     * @return boolean
     */
    public function delete(array $attributes): bool
    {
        $record = $this->firstOrFailByColumn($attributes);
        return $record->delete();
    }

    /**
     * Undocumented function
     *
     * @param integer $id
     * @param string $column
     * @param integer $amount
     * @param array $extra
     * @return integer
     */
    public function increment(int $id, string $column, int $amount = 1, array $extra = []): int
    {
        $record = $this->find($id);
        return $record->increment($column, $amount, $extra);
    }

    /**
     * Undocumented function
     *
     * @param integer $id
     * @param string $column
     * @param integer $amount
     * @param array $extra
     * @return integer
     */
    public function decrement(int $id, string $column, int $amount = 1, array $extra = []): int
    {
        $record = $this->find($id);
        return $record->decrement($column, $amount, $extra);
    }

    /**
     * Undocumented function
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->query()->get();
    }

    /**
     * Undocumented function
     *
     * @param Model $model
     * @return boolean
     */
    public function save(Model $model): bool
    {
        return $model->save();
    }

    /**
     * Undocumented function
     *
     * @param array $attributes
     * @param array $values
     * @return Model
     */
    public function firstOrCreate(array $attributes, array $values): Model
    {
        return $this->model->firstOrCreate($attributes, $values);
    }

    /**
     * Undocumented function
     *
     * @param integer $id
     * @param array $values
     * @param string $primaryKey
     * @return integer
     */
    public function updateById(int $id, array $values, string $primaryKey = 'id'): int
    {
        $record = $this->model->where($primaryKey, $id)->first();

        if (!$record) {
            throw new NotFoundException("Record not found", Response::HTTP_NOT_FOUND);
        }

        return $record->update($values);
    }
}
