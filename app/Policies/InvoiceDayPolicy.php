<?php

namespace App\Policies;

use App\Models\InvoiceDay;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoiceDayPolicy
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
        return $user->can('View invoice');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\InvoiceDay  $invoiceDay
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, InvoiceDay $invoiceDay)
    {
        return $user->can('View invoice');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Create invoice');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\InvoiceDay  $invoiceDay
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, InvoiceDay $invoiceDay)
    {
        return $user->can('Edit invoice');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\InvoiceDay  $invoiceDay
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, InvoiceDay $invoiceDay)
    {
        return $user->can('Delete invoice');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\InvoiceDay  $invoiceDay
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, InvoiceDay $invoiceDay)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\InvoiceDay  $invoiceDay
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, InvoiceDay $invoiceDay)
    {
        //
    }
}
