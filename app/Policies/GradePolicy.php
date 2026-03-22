<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;

class GradePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    public function view(User $user, Grade $grade): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    public function update(User $user, Grade $grade): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    public function restore(User $user, Grade $grade): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    public function forceDelete(User $user, Grade $grade): bool
    {
        return $user->hasRole('administrator');
    }
}
