<?php

namespace App\Jobs;

use App\Jobs\StarJob;
use App\Models\Customers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Events\AssignClaim as AssignClaimEvent;
use App\Models\Claim;

class RookieJob implements ShouldQueue
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
        Log::alert("Rookie");
        Log::error($this->customer->lastname);
        $downliners = Customers::where("upliner", $this->customer->referral_code)->where("level", 2)->count();
        if ($downliners == 4) {
            $this->customer->update([
                "level" => 2,
            ]);
            $this->customer->save();
            // job upliner of upliner

            $claim = Claim::where("level", '2')->first();
            event(new AssignClaimEvent($this->customer->id, $claim->id));

            $upliner = Customers::where("upliner", $this->customers->upliner)->first();
            if ($upliner) {
                StarJob::dispatch($upliner)->delay(now()->addMinutes(1));
                //send email
                Log::alert("move to star");
                return;
            }

        }

    }
}
