<?php

namespace App\Jobs;

use App\Events\AssignClaim as AssignClaimEvent;
use App\Jobs\SilverJob;
use App\Models\Claim;
use App\Models\Customers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BronzeJob implements ShouldQueue
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
        Log::alert("Bronze");
        Log::error($this->customer->lastname);
        if(!$this->customer->checkmln) {
            return; 
        }
        $downliners = Customers::where("upliner", $this->customer->referral_code)->where("level", 4)->count();
        if ($downliners == 4) {
            $this->customer->update([
                "level" => 4,
            ]);
            $this->customer->save();
            // job upliner of upliner


            if($this->customer->checkmln) {
              $claim = Claim::where("level", '4')->first();
              event(new AssignClaimEvent($this->customer->customer_id, $claim->id));   
            }

            $upliner = Customers::where("upliner", $this->customers->upliner)->first();
            if ($upliner) {
                SilverJob::dispatch($upliner)->delay(now()->addMinutes(1));
                //send email
                Log::alert("move to bronze");
                return;
            }
        }
    }
}
