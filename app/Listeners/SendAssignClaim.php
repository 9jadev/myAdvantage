<?php

namespace App\Listeners;

use App\Events\AssignClaim;
use App\Models\Claim;
use App\Models\ClaimAssignee;
use App\Models\Customers;
use App\Notifications\AssignClaimNotify;

class SendAssignClaim
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AssignClaim  $event
     * @return void
     */
    public function handle(AssignClaim $event)
    {

        $claim = Claim::where("id", $event->claim)->first();
        $data = ["claim_id" => $event->claim, "customer_id" => $event->customer, "type" => $claim->type];

        $createAssign = ClaimAssignee::create($data);
        $customer = Customers::where("customer_id", $event->customer)->first();
        logs()->info($event->customer);
        // logs()->info("No")
        $customer->notify((new AssignClaimNotify($claim, $customer))->delay([
            'mail' => now()->addMinutes(2),
            'sms' => now()->addMinutes(3),
        ]));

    }
}
