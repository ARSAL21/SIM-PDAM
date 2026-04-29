<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Pembayaran;
use Illuminate\Auth\Access\HandlesAuthorization;

class PembayaranPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Pembayaran');
    }

    public function view(AuthUser $authUser, Pembayaran $pembayaran): bool
    {
        return $authUser->can('View:Pembayaran');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Pembayaran');
    }

    public function update(AuthUser $authUser, Pembayaran $pembayaran): bool
    {
        return $authUser->can('Update:Pembayaran');
    }

    public function delete(AuthUser $authUser, Pembayaran $pembayaran): bool
    {
        return $authUser->can('Delete:Pembayaran');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Pembayaran');
    }

    public function restore(AuthUser $authUser, Pembayaran $pembayaran): bool
    {
        return $authUser->can('Restore:Pembayaran');
    }

    public function forceDelete(AuthUser $authUser, Pembayaran $pembayaran): bool
    {
        return $authUser->can('ForceDelete:Pembayaran');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Pembayaran');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Pembayaran');
    }

    public function replicate(AuthUser $authUser, Pembayaran $pembayaran): bool
    {
        return $authUser->can('Replicate:Pembayaran');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Pembayaran');
    }

}