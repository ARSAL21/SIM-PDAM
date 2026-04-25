<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PencatatanMeter;
use Illuminate\Auth\Access\HandlesAuthorization;

class PencatatanMeterPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PencatatanMeter');
    }

    public function view(AuthUser $authUser, PencatatanMeter $pencatatanMeter): bool
    {
        return $authUser->can('View:PencatatanMeter');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PencatatanMeter');
    }

    public function update(AuthUser $authUser, PencatatanMeter $pencatatanMeter): bool
    {
        return $authUser->can('Update:PencatatanMeter');
    }

    public function delete(AuthUser $authUser, PencatatanMeter $pencatatanMeter): bool
    {
        return $authUser->can('Delete:PencatatanMeter');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PencatatanMeter');
    }

    public function restore(AuthUser $authUser, PencatatanMeter $pencatatanMeter): bool
    {
        return $authUser->can('Restore:PencatatanMeter');
    }

    public function forceDelete(AuthUser $authUser, PencatatanMeter $pencatatanMeter): bool
    {
        return $authUser->can('ForceDelete:PencatatanMeter');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PencatatanMeter');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PencatatanMeter');
    }

    public function replicate(AuthUser $authUser, PencatatanMeter $pencatatanMeter): bool
    {
        return $authUser->can('Replicate:PencatatanMeter');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PencatatanMeter');
    }

}