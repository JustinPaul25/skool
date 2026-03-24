<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('student.{studentId}', function (User $user, string $studentId) {
    $studentId = (int) $studentId;

    return ($user->student !== null && (int) $user->student->id === $studentId)
        || $user->hasRole(['administrator', 'staff']);
});
