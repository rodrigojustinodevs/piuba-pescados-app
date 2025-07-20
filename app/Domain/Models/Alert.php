<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $company_id
 * @property string $alert_type
 * @property string $message
 * @property string $status
 *
 * @property-read Company $company
 */
class Alert extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'company_id',
        'alert_type',
        'message',
        'status',
    ];

    /** @var array<string> */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (Alert $alert): void {
            if (empty($alert->id)) {
                $alert->id = (string) Str::uuid();
            }
        });
    }

    /**
     * @phpstan-return BelongsTo<Company, static>
     */
    public function company(): BelongsTo
    {
        /** @var BelongsTo<Company, static> $relation */
        $relation = $this->belongsTo(Company::class, 'company_id');

        return $relation;
    }
}
