<?php

namespace App\Policies;

use App\Models\Permissions\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RolePolicy
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
        return $user->hasAnyPermission(['view roles']) 
            || $this->isSuperAdminOrDeveloper($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasAnyPermission(['view roles']) 
            || $this->isSuperAdminOrDeveloper($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['create roles']) 
            || $this->isSuperAdminOrDeveloper($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->hasAnyPermission(['edit roles']) 
            || $this->isSuperAdminOrDeveloper($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->hasAnyPermission(['delete roles']) 
            || $this->isSuperAdminOrDeveloper($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): bool
    {
        return $this->isSuperAdminOrDeveloper($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return $this->isSuperAdminOrDeveloper($user);
    }

    /**
     * Determine whether the user can attach a permission to a role.
     * Admin cannot attach permissions to roles.
     */
    public function attachPermission(User $user, Role $role): bool
    {
        // Super Admin and Developer can always attach
        if ($this->isSuperAdminOrDeveloper($user)) {
            return true;
        }

        // Admin cannot attach permissions to roles
        if ($this->isAdmin($user)) {
            return false;
        }

        // Others need permission
        return $user->hasPermissionTo('attach permission to role');
    }

    /**
     * Determine whether the user can detach a permission from a role.
     * Admin cannot detach permissions from roles.
     */
    public function detachPermission(User $user, Role $role): bool
    {
        // Super Admin and Developer can always detach
        if ($this->isSuperAdminOrDeveloper($user)) {
            return true;
        }

        // Admin cannot detach permissions from roles
        if ($this->isAdmin($user)) {
            return false;
        }

        // Others need permission
        return $user->hasPermissionTo('detach permission from role');
    }

    /**
     * Determine whether the user can attach a role to a user.
     */
    public function attachRoleToUser(User $user): bool
    {
        // Super Admin and Developer can always attach
        if ($this->isSuperAdminOrDeveloper($user)) {
            return true;
        }

        // Others need permission
        return $user->hasPermissionTo('attach role to user');
    }

    /**
     * Determine whether the user can detach a role from a user.
     * Admin cannot detach roles from users.
     */
    public function detachRoleFromUser(User $user): bool
    {
        // Super Admin and Developer can always detach
        if ($this->isSuperAdminOrDeveloper($user)) {
            return true;
        }

        // Admin cannot detach roles from users
        if ($this->isAdmin($user)) {
            return false;
        }

        // Others need permission
        return $user->hasPermissionTo('detach role from user');
    }
}
