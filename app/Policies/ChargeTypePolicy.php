<?php

namespace App\Policies;

use App\Models\ChargeType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChargeTypePolicy
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
        return $user->can('View charge type');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChargeType  $chargeType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ChargeType $chargeType)
    {
        return $user->can('View charge type');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Create charge type');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChargeType  $chargeType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ChargeType $chargeType)
    {
        return $user->can('Edit charge type');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChargeType  $chargeType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ChargeType $chargeType)
    {
        return $user->can('Delete charge type');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChargeType  $chargeType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, ChargeType $chargeType)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChargeType  $chargeType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, ChargeType $chargeType)
    {
        //
    }
}
