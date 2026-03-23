<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view users');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('view users');
    }

    public function create(User $user): bool
    {
        return $user->can('create users');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('update users');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('delete users') && $user->id !== $model->id;
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can('delete users');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->can('delete users') && $user->id !== $model->id;
    }
}
