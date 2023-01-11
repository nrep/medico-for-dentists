<?php

namespace App\Policies;

use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceProviderPolicy
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
        return $user->can('View service provider');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ServiceProvider  $serviceProvider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ServiceProvider $serviceProvider)
    {
        return $user->can('View service provider');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Create service provider');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ServiceProvider  $serviceProvider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ServiceProvider $serviceProvider)
    {
        return $user->can('Edit service provider');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ServiceProvider  $serviceProvider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ServiceProvider $serviceProvider)
    {
        return $user->can('Delete service provider');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ServiceProvider  $serviceProvider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, ServiceProvider $serviceProvider)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ServiceProvider  $serviceProvider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, ServiceProvider $serviceProvider)
    {
        //
    }
}
