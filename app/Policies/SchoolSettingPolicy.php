<?php

namespace App\Policies;

use App\Models\SchoolSetting;
use App\Models\User;

class SchoolSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage settings');
    }

    public function view(User $user, SchoolSetting $schoolSetting): bool
    {
        return $user->can('manage settings');
    }

    public function update(User $user, SchoolSetting $schoolSetting): bool
    {
        return $user->can('manage settings');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user, SchoolSetting $schoolSetting): bool
    {
        return false;
    }

    public function restore(User $user, SchoolSetting $schoolSetting): bool
    {
        return false;
    }

    public function forceDelete(User $user, SchoolSetting $schoolSetting): bool
    {
        return false;
    }
}
