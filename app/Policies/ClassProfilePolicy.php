<?php

namespace App\Policies;

use App\Models\ClassProfile;
use App\Models\User;

class ClassProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ClassProfile $classProfile): bool
    {
        return $classProfile->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ClassProfile $classProfile): bool
    {
        return $classProfile->user_id === $user->id;
    }

    public function delete(User $user, ClassProfile $classProfile): bool
    {
        return $classProfile->user_id === $user->id;
    }
}
