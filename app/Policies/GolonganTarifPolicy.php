<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GolonganTarif;
use Illuminate\Auth\Access\HandlesAuthorization;

class GolonganTarifPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GolonganTarif');
    }

    public function view(AuthUser $authUser, GolonganTarif $golonganTarif): bool
    {
        return $authUser->can('View:GolonganTarif');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GolonganTarif');
    }

    public function update(AuthUser $authUser, GolonganTarif $golonganTarif): bool
    {
        return $authUser->can('Update:GolonganTarif');
    }

    public function delete(AuthUser $authUser, GolonganTarif $golonganTarif): bool
    {
        return $authUser->can('Delete:GolonganTarif');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:GolonganTarif');
    }

    public function restore(AuthUser $authUser, GolonganTarif $golonganTarif): bool
    {
        return $authUser->can('Restore:GolonganTarif');
    }

    public function forceDelete(AuthUser $authUser, GolonganTarif $golonganTarif): bool
    {
        return $authUser->can('ForceDelete:GolonganTarif');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GolonganTarif');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GolonganTarif');
    }

    public function replicate(AuthUser $authUser, GolonganTarif $golonganTarif): bool
    {
        return $authUser->can('Replicate:GolonganTarif');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GolonganTarif');
    }

}