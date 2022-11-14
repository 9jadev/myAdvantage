<?php

namespace App\Jobs;

use App\Events\AssignClaim as AssignClaimEvent;
use App\Jobs\PlatinumJob;
use App\Models\Claim;
use App\Models\Customers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GoldJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $customer;
    public function __construct(Customers $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::alert("Gold");
        Log::error($this->customer->lastname);
        if(!$this->customer->checkmln) {
            return; 
        }
        $downliners = Customers::where("upliner", $this->customer->referral_code)->where("level", 6)->count();
        if ($downliners == 4) {
            $this->customer->update([
                "level" => 6,
            ]);
            $this->customer->save();
            // job upliner of upliner
            if($this->customer->checkmln) {
                $claim = Claim::where("level", '6')->first();
                event(new AssignClaimEvent($this->customer->id, $claim->id));
            }
            $upliner = Customers::where("upliner", $this->customers->upliner)->first();
            if ($upliner) {
                PlatinumJob::dispatch($upliner)->delay(now()->addMinutes(1));
                //send email
                Log::alert("move to platinum");
                return;
            }
        }
    }
}
