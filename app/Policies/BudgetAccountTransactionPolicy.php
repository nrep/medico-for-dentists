<?php

namespace App\Policies;

use App\Models\BudgetAccountTransaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BudgetAccountTransactionPolicy
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
        return $user->can('View budget account transaction');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BudgetAccountTransaction  $budgetAccountTransaction
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, BudgetAccountTransaction $budgetAccountTransaction)
    {
        return $user->can('View budget account transaction');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Create budget account transaction');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BudgetAccountTransaction  $budgetAccountTransaction
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, BudgetAccountTransaction $budgetAccountTransaction)
    {
        return $user->can('Edit budget account transaction');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BudgetAccountTransaction  $budgetAccountTransaction
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, BudgetAccountTransaction $budgetAccountTransaction)
    {
        return $user->can('Delete budget account transaction');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BudgetAccountTransaction  $budgetAccountTransaction
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, BudgetAccountTransaction $budgetAccountTransaction)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BudgetAccountTransaction  $budgetAccountTransaction
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, BudgetAccountTransaction $budgetAccountTransaction)
    {
        //
    }
}
