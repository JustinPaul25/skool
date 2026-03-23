<?php

namespace App\Services;

use App\Models\SchoolSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SettingsService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data): void
    {
        $setting = SchoolSetting::instance();

        DB::transaction(function () use ($setting, $data): void {
            $setting->update(Arr::only($data, $setting->getFillable()));
        });
    }
}
