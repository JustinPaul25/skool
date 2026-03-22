<?php

use App\Models\Student;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('student.{studentId}', function ($user, string $studentId) {
    $student = Student::query()->find($studentId);

    if (! $student) {
        return false;
    }

    if (! $student->user_id) {
        return false;
    }

    return (int) $user->id === (int) $student->user_id;
});
