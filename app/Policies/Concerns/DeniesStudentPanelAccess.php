<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait DeniesStudentPanelAccess
{
    /**
     * Students use the Inertia portal; Filament policies must not treat portal permissions as panel access.
     */
    protected function isStudent(User $user): bool
    {
        return $user->hasRole('student');
    }
}
