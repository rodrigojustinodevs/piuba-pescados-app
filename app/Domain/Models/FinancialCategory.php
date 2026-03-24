<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\FinancialCategoryStatus;
use App\Domain\Enums\FinancialType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string                    $id
 * @property string                    $company_id
 * @property string                    $name
 * @property FinancialType             $type
 * @property FinancialCategoryStatus   $status
 * @property-read Company|null         $company
 * @property Carbon|null               $created_at
 * @property Carbon|null               $updated_at
 * @property Carbon|null               $deleted_at
 */
class FinancialCategory extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'name',
        'type',
        'status',
    ];

    /** @var array<string, string|class-string> */
    protected $casts = [
        'type'   => FinancialType::class,
        'status' => FinancialCategoryStatus::class,
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (FinancialCategory $category): void {
            $category->id ??= (string) Str::uuid();
            $category->status ??= FinancialCategoryStatus::ACTIVE;
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

    /**
     * @phpstan-return HasMany<FinancialTransaction, static>
     */
    public function financialTransactions(): HasMany
    {
        /** @var HasMany<FinancialTransaction, static> $relation */
        $relation = $this->hasMany(FinancialTransaction::class, 'financial_category_id');

        return $relation;
    }

    public function hasTransactions(): bool
    {
        return $this->financialTransactions()->exists();
    }

    public function deactivate(): void
    {
        $this->update(['status' => FinancialCategoryStatus::INACTIVE]);
    }

    public function activate(): void
    {
        $this->update(['status' => FinancialCategoryStatus::ACTIVE]);
    }
}
