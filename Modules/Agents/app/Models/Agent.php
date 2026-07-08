<?php

namespace Modules\Agents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

// use Modules\Agents\Database\Factories\AgentFactory;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Agent> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Agents\Models\CreditTransaction> $creditTransaction
 * @property-read int|null $credit_transaction_count
 * @property-read Agent|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Agent query()
 * @mixin \Eloquent
 */
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

    public function creditTransaction(): HasMany {
        return $this->hasMany(CreditTransaction::class);
    }

    public function hasSufficientCredit(float $amount = 0): bool { 
        // $credit_limit = $this->credit_limit;
        // if($credit_limit < $amount) return false;
        // return true;

        return $this->credit_limit < $amount ? false : true;
    }

    public function deductCredit(float $amount = 0): bool { 
        if(!hasSufficientCredit($amount)) return false;
        try { 
            return DB::transaction(function() use ($amount) { 
                // this is the same of but slower cause of __callstatic() function take sometime to create eloquent builder and pass where function to it 
                // $agent = static::where('id', $this->id)->lockForUpdate()->first();
                $agent = static::query()->where('id', $this->id)->lockForUpdate()->first();
                
                $agent->credit_limit = $agent->credit_limit - $amount;
                $agent->credit_used = $agent->credit_used + $amount;
                
                // $this->credit_limit = $this->credit_limit - $amount;
                // $this->credit_used = $this->credit_used + $amount;

                // save it in database
                $agent->save();

                // update the current object with new credits data
                $this->fill($agent->toArray());

                return true;
            });
        } catch(Throwable $e) { 
            Log::error($e->getMessage());
            return false;
        }
    }

    public function addCredit(float $amount): bool { 
        try { 
            return DB::transaction(function() use ($amount) { 
                $agent = static::query()->whereKey($this->id)->lockForUpdate()->first();
                
                // calculations : 
                $agent->credit_limit = $agent->credit_limit + $amount;
                $agent->credit_used = $agent->credit_used - $amount;

                // check for validation for credit used must be 0 or positive number
                if($agent->credit_used < 0) return false;

                $agent->save();

                $this->fill($agent->toArray());

                // $this->credit_limit = $this->credit_limit + $amount;
                // $this->credit_used = $this->credit_used - $amount;
                
                // if($this->credit_used < 0) return false;

                // $this->save();

                return true;
            });
        } catch (Throwable $e) { 
            Log::error($e->getMessage());
            return false;
        }
    }

    public function applyCreditBalanceDelta(float $balanceDelta): void { 
        // if the balance delta is positive then the caculation will be an add credit else its gonna be deduct credit
        if($balanceDelta > 0) { 
            try {
                DB::transaction(function() use ($balanceDelta) {
                    $agent = static::query()->whereKey($this->id)->lockForUpdate()->first();
                    
                    $agent->credit_limit = $agent->credit_limit - $balanceDelta;
                    $agent->credit_used = $agent->credit_used + $balanceDelta;
                    
                    $agent->save();
                    
                    $this->fill($agent->toArray());
                });
            } catch(Throwable $e) { 
                Log::error($e->getMessage());
            }
        } 
        else { 
            try {
                DB::transaction(function() use ($balanceDelta) {
                    $agent = static::query()->whereKey($this->id)->lockForUpdate()->first();
                    
                    $absBalanceDelta = abs($balanceDelta);

                    $agent->credit_limit = $agent->credit_limit + $absBalanceDelta;
                    $agent->credit_used = $agent->credit_used - $absBalanceDelta;
                    
                    $agent->save();
                    
                    $this->fill($agent->toArray());
                });
            } catch(Throwable $e) { 
                Log::error($e->getMessage());
            }
        }
    }

    public function getEffectiveCreditLimit(): ?string { 
        // if($this->credit_limit > 0 || $this->credit_limit !== null) return (string) $this->credit_limit;     
        // if(is_null($this->parent_agent_id)) return null;
        // $parent_agent = static::query()->where('id', $this->parent_agent_id)->first(); 
        // if($parent_agent->credit_limit > 0 || $parent_agent->credit_limit !== null) return (string) $this->credit_limit;

        $credit_limit = $this->credit_limit;
        $agent = $this;
        while(true) { 
            if($this->hasOwnCreditLimit($credit_limit)) return (string) $credit_limit;
            if(!$this->hasParent($agent)) return null;
            $agent = static::query()->where('id', $agent->parent_agent_id)->first();
            $credit_limit = $agent->credit_limit;
        } 
    }

    public function getEffectiveCreditUsed(): ?string { 
        $credit_used = $this->credit_used;
        $agent = $this;
        while(true) { 
            if($this->credit_used !== null) return (string) $credit_used;
            if(!hasParent($agent)) return null;
            $agent = static::query()->whereKey($agent->parent_agent_id)->first();
            $credit_used = $agent->credit_used;
        }
    }

    public function hasOwnCreditLimit($credit_limit): bool { 
        // if( $credit_limit !== null ) return true; 
        // return false;
        return $credit_limit !== null;
    }

    public function hasParent(Agent $agent): bool { 
        // if(is_null($agent->parent_agent_id)) return false;
        // return true;
        return is_null($agent->parent_agent_id) ? false : true;
    }

    public function getCreditUsed(): float { 
        return $this->credit_used > 0 ? $this->credit_used : 0;
    }

    public function getCreditCeiling(): float { 
        if($this->hasOwnCreditLimit($this->credit_limit)) return (float) $this->credit_limit + (float) $this->credit_used;
        $credit_limit = $this->getEffectiveCreditLimit();
        // $credit_used = $this->getEffectiveCreditUsed();
        $credit_used = $this->getCreditUsed();
        return $credit_limit + $credit_used; 
    }
}
