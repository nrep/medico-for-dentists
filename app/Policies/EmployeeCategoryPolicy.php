<?php

namespace App\Policies;

use App\Models\EmployeeCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeeCategoryPolicy
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
        return $user->can('View employee category');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmployeeCategory  $employeeCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, EmployeeCategory $employeeCategory)
    {
        return $user->can('View employee category');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Create employee category');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmployeeCategory  $employeeCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, EmployeeCategory $employeeCategory)
    {
        return $user->can('Edit employee category');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmployeeCategory  $employeeCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, EmployeeCategory $employeeCategory)
    {
        return $user->can('Delete employee category');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmployeeCategory  $employeeCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, EmployeeCategory $employeeCategory)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmployeeCategory  $employeeCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, EmployeeCategory $employeeCategory)
    {
        //
    }
}
