<?php

namespace App\Jobs;

use App\Models\Customers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PlatinumJob implements ShouldQueue
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
        $downliners = Customers::where("upliner", $this->customer->referral_code)->where("level", 7)->count();
        if ($downliners == 4) {
            $this->customer->update([
                "level" => 7,
            ]);
            $this->customer->save();
            // job upliner of upliner

            // $upliner = Customers::where("upliner", $this->customers->upliner)->first();
            // if ($upliner) {
            //     PlatinumJob::dispatch($upliner)->delay(now()->addMinutes(1));
            //     //send email
            //     Log::alert("move to platinum");
            //     return;
            // }
        }
    }
}
