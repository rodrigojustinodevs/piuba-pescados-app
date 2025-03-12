<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $name
 *
 * @property-read Tank[] $tanks
 */
class TankType extends BaseModel
{
    protected $table = 'tank_types';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'name'];

    #[\Override]
    public function save(array $options = [])
    {
        return false;
    }

    #[\Override]
    public function delete()
    {
        return false;
    }

    /**
     * @phpstan-return HasMany<Tank, static>
     */
    public function tanks(): HasMany
    {
        /** @var HasMany<Tank, static> $relation */
        $relation = $this->hasMany(Tank::class, 'tank_type_id'); // Correção do nome da chave

        return $relation;
    }
}
