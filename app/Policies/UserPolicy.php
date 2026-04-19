<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin-PDAM');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasRole('admin-PDAM');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin-PDAM');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasRole('admin-PDAM');
    }

    /**
     * Lapis 3: Proteksi Inti - Menghapus User
     */
    public function delete(User $user, User $model): bool
    {
        // 1. Blokir Keras: Super Admin (admin-PDAM) tidak boleh dihapus sama sekali
        if ($model->hasRole('admin-PDAM')) {
            return false;
        }

        // 2. Blokir Keras: Jangan biarkan admin menghapus dirinya sendiri
        if ($user->id === $model->id) {
            return false;
        }

        // Izinkan jika yang mencoba menghapus adalah admin-PDAM
        return $user->hasRole('admin-PDAM');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin-PDAM');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasRole('admin-PDAM');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        if ($model->hasRole('admin-PDAM')) {
            return false;
        }
        return $user->hasRole('admin-PDAM');
    }
}
