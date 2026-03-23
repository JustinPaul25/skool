<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\User;

class SectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view sections');
    }

    public function view(User $user, Section $section): bool
    {
        return $user->can('view sections');
    }

    public function create(User $user): bool
    {
        return $user->can('create sections');
    }

    public function update(User $user, Section $section): bool
    {
        return $user->can('update sections');
    }

    public function delete(User $user, Section $section): bool
    {
        return $user->can('delete sections');
    }

    public function restore(User $user, Section $section): bool
    {
        return $user->can('delete sections');
    }

    public function forceDelete(User $user, Section $section): bool
    {
        return $user->can('delete sections') && $user->hasRole('administrator');
    }
}
