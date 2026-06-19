<?php

namespace App\Policies;

use App\Models\User;

class BasePolicy
{
    protected function can(User $user, string $permission): bool
    {
        return $user->can($permission);
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, $this->module().'.view');
    }

    public function view(User $user, $model): bool
    {
        return $this->can($user, $this->module().'.view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, $this->module().'.add');
    }

    public function update(User $user, $model): bool
    {
        return $this->can($user, $this->module().'.edit');
    }

    public function delete(User $user, $model): bool
    {
        return $this->can($user, $this->module().'.delete');
    }

    protected function module(): string
    {
        // Override in subclass
        return '';
    }
}
