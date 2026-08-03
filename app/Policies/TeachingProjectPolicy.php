<?php

namespace App\Policies;

use App\Models\TeachingProject;
use App\Models\User;

class TeachingProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TeachingProject $teachingProject): bool
    {
        return $teachingProject->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TeachingProject $teachingProject): bool
    {
        return $teachingProject->user_id === $user->id;
    }

    public function delete(User $user, TeachingProject $teachingProject): bool
    {
        return $teachingProject->user_id === $user->id;
    }
}
