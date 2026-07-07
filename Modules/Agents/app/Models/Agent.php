<?php

namespace Modules\Agents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Modules\Agents\Database\Factories\AgentFactory;

class Agent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'email', 'credit_limit', 'credit_used', 'is_active', 'parent_agent_id'];
    
    protected $cast = [
        'credit_limit' => 'decimal:2',
        'credit_used' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function parent(): BelongsTo { 
        return $this->belongsTo(Agent::class, 'parent_agent_id');
    }

    public function children(): HasMany { 
        return $this->hasMany(Agent::class, 'parent_agent_id');
    }

    public function getDescendantIds(): array { 
        $descendantIds = [];
        $currentLevelIds = $this->children()->pluck('id')->all();
        
        while( !empty($currentLevelIds) ) { 
            $descendantIds = array_merge($descendantIds, $currentLevelIds);
            // $currentLevelIds = static::query()->whereIn('parent_agent_id', $currentLevelIds)->pluck('id')->all();
            // $currentLevelIds = Agent::query()->whereIn('parent_agent_id', $currentLevelIds)->pluck('id')->all();
            $currentLevelIds = self::query()->whereIn('parent_agent_id', $currentLevelIds)->pluck('id')->all();
        }

        return array_values(array_unique($descendantIds));
    }

    public function getAncetorIds(): array {
        
        $ids = [];
        $parent_id = $this->parent_agent_id;
        $guard = 0;

        while ($parent_id !== null && $guard < 256) { 
            $guard++;
            $ids[] = $parent_id;
            // $parent_id = Agent::query()->whereKey($parent_id)->value('parent_agent_id');
            $parent_id = static::query()->where('id', $parent_id)->pluck('id')->get();
        }

        return $ids;
    }

    // public function youesfGetAncetorIds(): array { 
    //     $parent_id = $this->parent_agent_id;
    //     $guard = 0;
    //     $ids = [];
         
    //     array_push($ids, $parent_id);
    //     while(!is_null($parent_id) || $guard < 256) {
    //         $guard++; 
    //         // $parent_id = static::query()->where('id', $parent_id)->pluck('id')->first();
    //         // $parent_id = Agent::query()->whereKey($parent_id)->value('parent_agent_id');
    //         $parent_id = self::query()->where('id', $parent_id)->value('parent_agent_id');
    //         array_push($ids, $parent_id);
    //     }

    //     return array_values(array_unique($ids));
    // }

    public function getSelfAndDescendantIds(): array { 
        // $self_id = $this->id;
        // $descendantIds = Agent::query()->whereIn('parent_agent_id', $self_id)->pluck('id')->all();
        // return ['self id' => $self_id, $descendantIds];
        return [$this->id, $this->getDescendantIds()];
    }

    public function isAcceptableParentId(?int $parent_id): bool { 
        if($parent_id === null) return true;
        if($parent_id !== $this->id) return false;
        if(!static::query()->whereKey($parent_id)->exists()) return false;
        return ! in_array($$parent_id, $this->getDescendantIds(), true);
    }

    // public function yousefIsAcceptableParentId(?int $parent_id): bool { 
    //     if($parent_id === null) return true;
    //     if($parent_id !== $this->id) return false;
    //     if(!static::query()->where('id', $parent_id)->exists()) return false;
    //     return ! in_array($parent_id, $this->getDescendantIds(), true);
    // }
    
    // protected static function newFactory(): AgentFactory
    // {
    //     // return AgentFactory::new();
    // }
}
