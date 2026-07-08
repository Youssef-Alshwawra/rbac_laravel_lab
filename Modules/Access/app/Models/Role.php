<?php

namespace Modules\Access\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Modules\Access\Database\Factories\RoleFactory;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Access\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Access\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
