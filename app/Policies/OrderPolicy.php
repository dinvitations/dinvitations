<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::VIEW_ANY_ORDERS);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::VIEW_ORDERS)
            && $user->id === $order->customer?->organizer->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::CREATE_ORDERS);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::EDIT_ORDERS)
            && $user->id === $order->customer?->organizer->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::DELETE_ORDERS)
            && $user->id === $order->customer?->organizer->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return false;
    }
}
