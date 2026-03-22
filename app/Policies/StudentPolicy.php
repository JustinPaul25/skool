<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'staff', 'branch_manager']);
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->hasAnyRole(['administrator', 'staff'])) {
            return true;
        }

        if ($user->hasRole('branch_manager')) {
            return (int) $user->branch_id === (int) $student->branch_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'staff', 'branch_manager']);
    }

    public function update(User $user, Student $student): bool
    {
        if ($user->hasAnyRole(['administrator', 'staff'])) {
            return true;
        }

        if ($user->hasRole('branch_manager')) {
            return (int) $user->branch_id === (int) $student->branch_id;
        }

        return false;
    }

    public function delete(User $user, Student $student): bool
    {
        if ($user->hasAnyRole(['administrator', 'staff'])) {
            return true;
        }

        if ($user->hasRole('branch_manager')) {
            return (int) $user->branch_id === (int) $student->branch_id;
        }

        return false;
    }

    public function restore(User $user, Student $student): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    public function forceDelete(User $user, Student $student): bool
    {
        return $user->hasRole('administrator');
    }
}
