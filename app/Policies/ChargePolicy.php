<?php

namespace App\Policies;

use App\Models\Charge;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChargePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->can('View charge');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Charge  $charge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Charge $charge)
    {
        return $user->can('View charge');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Create charge');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Charge  $charge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Charge $charge)
    {
        return $user->can('Edit charge');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Charge  $charge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Charge $charge)
    {
        return $user->can('Delete charge');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Charge  $charge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Charge $charge)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Charge  $charge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Charge $charge)
    {
        //
    }
}
