<?php
namespace App\Http\Traits;


trait CanTrait {

    /**
     * @param string|array $roles
     */
    public function authorizeRoles($roles)
    {
        if (is_array($roles)) {
            return $this->hasAnyRole($roles);
        }
        return $this->hasRole($roles);
    }

    /**
     * Check multiple roles
     * @param array $roles
     */
    public function hasAnyRole($roles)
    {
        return null !== $this->role()->whereIn('name', $roles)->first();
    }

    /**
     * Check one role
     * @param string $role
     */
    public function hasRole($role)
    {
        return null !== $this->role()->where('name', $role)->first();
    }

    public function ownProject($project)
    {
        return null !== $this->project()->where('id', $project)->first();
    }
}