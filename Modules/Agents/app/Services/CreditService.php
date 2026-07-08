<?php

namespace Modules\Agents\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Agents\Models\Agent;
use Modules\Agents\Models\CreditTransaction;
use RuntimeException;
use Throwable;

class CreditService
{
    public function charge(int $agentId, float $amount, string $description): void { 
        try {
            DB::transaction(function() use ($agentId, $amount, $description) {
                
                /** @var Agent $agent */
                $agent = Agent::query()->where('id', $agentId)->lockForUpdate()->firstOrFail();
                if(!$agent->hasSufficientCredit($amount)) throw new RuntimeException('Agent credit limit exceeded.');
                
                // save balance before we deduct credit
                $balance_before = $agent->credit_limit;

                // deduct credit from the agent
                $agent->deductCredit($amount);
                
                // save agent after deduct in db
                $agent->save();

                // add new transaction in credit transication table
                $creditTransaction = new CreditTransaction();

                $creditTransaction->agent_id = $agentId;
                $creditTransaction->amount = $amount;
                $creditTransaction->description = $description;
                $creditTransaction->type = 'booking';
                $creditTransaction->balance_before = $balance_before;
                $creditTransaction->balance_after = $agent->credit_limit;

                // save the new transaction in db
                $creditTransaction->save();

            });
        } catch (Throwable $e) { 
            Log::error($e->getMessage());
        }
    }
}
