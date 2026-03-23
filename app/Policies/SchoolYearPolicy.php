<?php

namespace App\Policies;

use App\Models\SchoolYear;
use App\Models\User;

class SchoolYearPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view school years');
    }

    public function view(User $user, SchoolYear $schoolYear): bool
    {
        return $user->can('view school years');
    }

    public function create(User $user): bool
    {
        return $user->can('create school years');
    }

    public function update(User $user, SchoolYear $schoolYear): bool
    {
        return $user->can('update school years');
    }

    public function delete(User $user, SchoolYear $schoolYear): bool
    {
        return $user->can('delete school years') && ! $schoolYear->is_active;
    }

    public function restore(User $user, SchoolYear $schoolYear): bool
    {
        return $user->can('delete school years');
    }

    public function forceDelete(User $user, SchoolYear $schoolYear): bool
    {
        return $user->can('delete school years') && ! $schoolYear->is_active;
    }
}
