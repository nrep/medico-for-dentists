<?php

namespace App\Policies;

use App\Models\ChargeList;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChargeListPolicy
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
        return $user->can('View charge list');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChargeList  $chargeList
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ChargeList $chargeList)
    {
        return $user->can('View charge list');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Create charge list');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChargeList  $chargeList
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ChargeList $chargeList)
    {
        return $user->can('Edit charge list');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChargeList  $chargeList
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ChargeList $chargeList)
    {
        return $user->can('Delete charge list');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChargeList  $chargeList
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, ChargeList $chargeList)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChargeList  $chargeList
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, ChargeList $chargeList)
    {
        //
    }
}
