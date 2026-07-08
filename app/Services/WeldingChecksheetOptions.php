<?php

namespace App\Services;

use App\Models\User;
use App\Models\WeldingChecksheetType;
use App\Models\WeldingItemConfig;
use Illuminate\Support\Collection;

class WeldingChecksheetOptions
{
    public function types(): Collection
    {
        return WeldingChecksheetType::with([
            'itemConfigs' => fn ($query) => $query->active()->orderBy('item_code'),
        ])
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function importTypes(): Collection
    {
        return WeldingChecksheetType::with([
            'itemConfigs' => fn ($query) => $query->orderBy('item_code'),
        ])
            ->orderBy('name')
            ->get();
    }

    public function itemCodes(): Collection
    {
        return WeldingItemConfig::query()
            ->active()
            ->orderBy('item_code')
            ->pluck('item_code')
            ->unique()
            ->values();
    }

    public function users(): Collection
    {
        return User::select('id', 'name')->orderBy('name')->get();
    }

    public function formOptions(): array
    {
        return [
            'users' => $this->users(),
            'types' => $this->types(),
        ];
    }

    public function importOptions(): array
    {
        return [
            'users' => $this->users(),
            'types' => $this->importTypes(),
        ];
    }
}
