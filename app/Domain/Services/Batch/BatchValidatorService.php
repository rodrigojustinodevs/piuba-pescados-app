<?php

declare(strict_types=1);

namespace App\Domain\Services\Batch;

use App\Domain\Models\Batch;
use Illuminate\Validation\ValidationException;

class BatchValidatorService
{
    public function validateActiveBatch(Batch $batch): void
    {
        if ($batch->status !== 'active') {
            throw ValidationException::withMessages([
                'batch_id' => 'Não é possível registrar arraçoamento em lote inativo ou finalizado.',
            ]);
        }
    }
}
