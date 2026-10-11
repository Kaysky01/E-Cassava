<?php

namespace App\Policies;

use App\Models\MeasurementSession;
use App\Models\User;

class MeasurementSessionPolicy
{
    public function view(User $user, MeasurementSession $session): bool
    {
        return $session->device?->user_id === $user->id;
    }

    public function update(User $user, MeasurementSession $session): bool
    {
        return $session->device?->user_id === $user->id;
    }
}
