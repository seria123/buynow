<?php

namespace App\Policies;

use App\Models\Permissions\Permission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PermissionPolicy
{
    /**
     * Check if user is Super Admin or Developer (full access).
     */
    private function isSuperAdminOrDeveloper(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Developer']);
    }

    /**
     * Check if user is Admin.
     */
    private function isAdmin(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view permissions']) 
            || $this->isSuperAdminOrDeveloper($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Permission $permission): bool
    {
        return $user->hasAnyPermission(['view permissions']) 
            || $this->isSuperAdminOrDeveloper($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['create permissions']) 
            || $this->isSuperAdminOrDeveloper($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Permission $permission): bool
    {
        return $user->hasAnyPermission(['edit permissions']) 
            || $this->isSuperAdminOrDeveloper($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Permission $permission): bool
    {
        return $user->hasAnyPermission(['delete permissions']) 
            || $this->isSuperAdminOrDeveloper($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Permission $permission): bool
    {
        return $this->isSuperAdminOrDeveloper($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Permission $permission): bool
    {
        return $this->isSuperAdminOrDeveloper($user);
    }
}
