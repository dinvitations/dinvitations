<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GuestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::VIEW_ANY_GUESTS);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Guest $guest): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::VIEW_GUESTS)
            && $guest->guest_group?->customer_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::CREATE_GUESTS);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Guest $guest): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::EDIT_GUESTS)
            && $guest->guest_group?->customer_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Guest $guest): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::VIEW_GUESTS)
            && $guest->guest_group?->customer_id === $user->id;
    }
}
