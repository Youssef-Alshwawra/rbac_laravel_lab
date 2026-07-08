<?php

namespace Modules\Access\Models;

// use Database\Factories\UserFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Modules\Agents\Models\Agent;

// use Modules\Access\Database\Factories\UserFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $parent_id
 * @property int|null $role_id
 * @property-read Agent|null $agent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read User|null $parent
 * @property-read \Modules\Access\Models\Role|null $role
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use /** HasFactory, */ Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'email', 'password', 'parent_id', 'role_id', 'agent_id'];
    protected $guarded = ['id'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array { 
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // protected static function newFactory(): UserFactory
    // {
    //     return \Modules\Access\Database\Factories\UserFactory::new();
    // }

    public function parent(): BelongsTo { 
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children(): HasMany { 
        return $this->hasMany(User::class, 'parent_id');
    }

    // public function getDescendantIds(): array {
        
    //     $descendantIds = [];
    //     $currentLevelIds = $this->children()->pluck('id')->all();

    //     while( ! empty($currentLevelIds) ) { 
    //         $descendantIds = array_merge($descendantIds, $currentLevelIds); 
    //         $currentLevelIds = static::query()->whereIn('parent_id', $currentLevelIds)->pluck('id')->all();
    //     }

    //     return array_values(array_unique($descendantIds));
    // }

    public function getDescendantIds(): array { 
        $descendantIds = [];
        $currentLevelIds = $this->children()->pluck('id')->all();

        while( ! empty($currentLevelIds) ) { 
            $descendantIds = array_merge($descendantIds ,$currentLevelIds);
            $currentLevelIds = static::query()->whereIn('parent_id', $currentLevelIds)->pluck('id')->all();
        }

        return array_values(array_unique($descendantIds));
    }

    public function role(): BelongsTo { 
        return $this->belongsTo(Role::class);
    }

    public function hasPermission(string $permissionSlug): bool { 
        if(! $this->role) {
            return false;
        }
        return $this->role->hasPermission($permissionSlug);
    }

    public function agent(): BelongsTo { 
        return $this->belongsTo(Agent::class);
    }

    // public function isRootBookingUser(): bool { 
    //     $agent = $this->agent_id;
        
    //     if($agent && $agent !== null) { 
    //         return false;
    //     }

    //     return true;
    // }

    public function isRootBookingUser(): bool { 
        // return is_null($this->agent_id);
        // or this:
        return $this->agent_id === null;
    }

    // public function yousefIsRootBookingUser(): bool { 
    //     if(is_null($this->agent)) return true;
    //     return false;
    // }

    public function isFirstLevelAgentUser(): bool {
        
        if($this->agent_id === null || is_null($this->agent_id)) return false;

        $agent = Agent::query()->where('id', $this->agent_id)->first();
        
        if($agent === null) return false;

        if($agent->parent_agent_id === null || is_null($agent->agent_parent_id)) return true;
        
        return false;
    }

    public function isSecondLevelAgentUser(): bool { 
        // $agent = Agent::query()->where('id', $this->agent_id)->get();
        // if($agent->parent_agent_id === null || is_null($agent->parent_agent_id)) return false;
        // return true;
        if($this->agent_id === null) return false;

        $agent = Agent::query()->whereKey($this->agent_id)->first();

        if($agent === null) return false;

        if($agent->parent_agent_id !== null) return true;

        return false;
    }
}
