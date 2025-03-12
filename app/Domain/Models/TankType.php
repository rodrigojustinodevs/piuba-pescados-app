<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class TankType extends BaseModel
{
    protected $table = 'tank_types';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'name'];

    public function save(array $options = [])
    {
        return false;
    }

    public function delete()
    {
        return false;
    }


    /**
     * @phpstan-return BelongsToMany<Tank, static>
     */
    public function tanks(): HasMany
    {
        /** @var HasMany<Tank, static> $relation */
        $relation = $this->hasMany(Tank::class, 'tank_types_id');

        return $relation;
    }
}
