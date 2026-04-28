<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Tagihan;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagihanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Tagihan');
    }

    public function view(AuthUser $authUser, Tagihan $tagihan): bool
    {
        return $authUser->can('View:Tagihan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Tagihan');
    }

    public function update(AuthUser $authUser, Tagihan $tagihan): bool
    {
        return $authUser->can('Update:Tagihan');
    }

    public function delete(AuthUser $authUser, Tagihan $tagihan): bool
    {
        return $authUser->can('Delete:Tagihan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Tagihan');
    }

    public function restore(AuthUser $authUser, Tagihan $tagihan): bool
    {
        return $authUser->can('Restore:Tagihan');
    }

    public function forceDelete(AuthUser $authUser, Tagihan $tagihan): bool
    {
        return $authUser->can('ForceDelete:Tagihan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Tagihan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Tagihan');
    }

    public function replicate(AuthUser $authUser, Tagihan $tagihan): bool
    {
        return $authUser->can('Replicate:Tagihan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Tagihan');
    }

}