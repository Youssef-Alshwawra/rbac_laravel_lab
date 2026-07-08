<?php

namespace Modules\Agents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Modules\Agents\Database\Factories\CreditTransactionFactory;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction query()
 * @property-read \Modules\Agents\Models\Agent|null $agent
 * @mixin \Eloquent
 */
class CreditTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['agent_id', 'type', 'amount', 'balance_before', 'balance_after', 'description'];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ]; 

    public function agent(): BelongsTo { 
        return $this->belongsTo(Agent::class, 'agent_id');
    }
    
    
    // protected static function newFactory(): CreditTransactionFactory
    // {
    //     // return CreditTransactionFactory::new();
    // }
}
