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

    // public function refund(int $agentId, float $amount, string $description): void { 
    //     if($amount <= 0) return;
    //     try { 
    //         DB::transaction(function() use ($agentId, $amount, $description) {
    //             $agent = Agent::query()->whereKey($agentId)->lockForUpdate()->firstOrFail();

    //             // if(!$agent->hasSufficientCredit($amount)) throw new RuntimeException;

    //             $creditTransaction = new CreditTransaction();
    //             $creditTransaction->balance_before = $agent->credit_limit;

    //             $agent->addCredit($amount, true);
    //             // $agent->save();

    //             $creditTransaction->agent_id = $agentId;
    //             $creditTransaction->balance_after = $agent->fresh()->credit_limit;
    //             $creditTransaction->amount = -$amount;
    //             $creditTransaction->type = 'cancellation';
    //             $creditTransaction->description = $description;

    //             $creditTransaction->save();

    //         });
    //     } catch (Throwable $e) { 
    //         Log::error($e->getMessage());
    //     }
    // }

    // prac1
    // public function refund(int $agentId, float $amount, string $description): void { 
    //     if($amount <= 0) return;
    //     DB::transaction(function () use ($agentId, $amount, $description) {
    //         // lock agent for update
    //         $agent = Agent::query()->where('id', $agentId)->lockForUpdate()->firstOrFail();
            
    //         // create new credit transaction to save the transaction details
    //         $creditTransaction = new CreditTransaction();
            
    //         // we should save the credit limit before refund
    //         // $creditTransaction->balance_before = $agent->credit_limit;
    //         $creditTransaction->balance_before = $agent->getEffectiveCreditLimit();
    //         $agent->addCredit($amount, true);

    //         $creditTransaction->agent_id = $agentId;
    //         $creditTransaction->type = 'cancellation';
    //         $creditTransaction->amount = -abs($amount);
    //         // $creditTransaction->balance_after = $agent->fresh()->credit_limit;
    //         $creditTransaction->balance_after = $agent->fresh()->getEffectiveCreditLimit();
    //         $creditTransaction->description = $description;

    //         $creditTransaction->save();
    //     });
    // }

    //another approach
    public function refund(int $agentId, float $amount, string $description): void { 
        try { 
            if($amount <= 0) { 
                return;
            } 
                
            DB::transaction(function() use ($agentId, $amount, $description) {
                $agent = Agent::query()->whereKey($agentId)->lockForUpdate()->firstOrFail();
                
                $balanceBefore = $agent->getEffectiveCreditLimit();
                
                $agent->addCredit($amount, true);

                $balanceAfter = $agent->fresh()->getEffectiveCreditLimit();
                
                CreditTransaction::create([
                    'agent_id' => $agentId,
                    'type' => 'cancellation',
                    'amount' => -abs($amount),
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'description' => $description,
                ]);
            });
        } catch(Throwable $e) { 
            Log::error($e->getMessage());
        }
    }
}
