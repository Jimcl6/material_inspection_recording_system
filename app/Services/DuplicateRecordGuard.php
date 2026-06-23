<?php

namespace App\Services;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DuplicateRecordGuard
{
    /**
     * Run a create operation only when no matching record already exists.
     *
     * @template T
     *
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $criteria
     * @param  Closure(): T  $create
     * @return T
     */
    public function create(
        string $modelClass,
        array $criteria,
        string $recordLabel,
        Closure $create
    ) {
        ksort($criteria);

        $lockKey = 'duplicate-record:' . sha1($modelClass . json_encode($criteria));

        return Cache::lock($lockKey, 10)->block(5, function () use (
            $modelClass,
            $criteria,
            $recordLabel,
            $create
        ) {
            return DB::transaction(function () use ($modelClass, $criteria, $recordLabel, $create) {
                $existing = $modelClass::query()->where($criteria)->first();

                if ($existing) {
                    throw ValidationException::withMessages([
                        'duplicate' => "A matching {$recordLabel} already exists (Record #{$existing->getKey()}). "
                            . 'The duplicate was not saved, and the existing record was kept unchanged.',
                    ]);
                }

                return $create();
            });
        });
    }
}
