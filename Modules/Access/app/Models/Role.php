<?php

namespace Modules\Access\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Modules\Access\Database\Factories\RoleFactory;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'description'];

    // protected static function newFactory(): RoleFactory
    // {
    //     // return RoleFactory::new();
    // }

    public function users(): HasMany { 
        return $this->hasMany(User::class);
    }

    public function permissions(): BelongsToMany { 
        return $this->belongsToMany(Permission::class, 'role_permission', 'role_id', 'permission_id')->withTimestamps();
    }

    public function hasPermission(string $permissionSlug): bool { 
        // simple optimization
        if($this->relationLoaded('permissions')) { 
            return $this->permissions->contains('slug', $permissionSlug);
        }

        return $this->permissions()
                    ->where('slug', $permissionSlug)
                    ->exists();
    }
}
