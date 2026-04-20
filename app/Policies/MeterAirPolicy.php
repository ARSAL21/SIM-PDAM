<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MeterAir;
use Illuminate\Auth\Access\HandlesAuthorization;

class MeterAirPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MeterAir');
    }

    public function view(AuthUser $authUser, MeterAir $meterAir): bool
    {
        return $authUser->can('View:MeterAir');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MeterAir');
    }

    public function update(AuthUser $authUser, MeterAir $meterAir): bool
    {
        return $authUser->can('Update:MeterAir');
    }

    public function delete(AuthUser $authUser, MeterAir $meterAir): bool
    {
        return $authUser->can('Delete:MeterAir');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MeterAir');
    }

    public function restore(AuthUser $authUser, MeterAir $meterAir): bool
    {
        return $authUser->can('Restore:MeterAir');
    }

    public function forceDelete(AuthUser $authUser, MeterAir $meterAir): bool
    {
        return $authUser->can('ForceDelete:MeterAir');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MeterAir');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MeterAir');
    }

    public function replicate(AuthUser $authUser, MeterAir $meterAir): bool
    {
        return $authUser->can('Replicate:MeterAir');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MeterAir');
    }

}