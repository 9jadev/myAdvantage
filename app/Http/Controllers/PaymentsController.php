<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payments\ManualPaymentRequest;
use App\Models\Payments;

class PaymentsController extends Controller
{
    public function manualpayments(ManualPaymentRequest $request)
    {
        // return $request;
        $data = $request->validated();
        $manualpayments = Payments::where("reference", $request->ref)->first();
        // return $manualpayments;
        if (!$manualpayments) {
            return response()->json(["message" => "No payments", "status" => "error"], 400);
        }
        if ($manualpayments->status == "1") {
            return response()->json(["message" => "Payment already completed", "status" => "error"], 400);
        }
        $data = array_merge($data, [
            "status" => "2",
        ]);
        $manualpayments->update($data);
        $manualpayments->refresh();
        return response()->json(["message" => "Awaiting verifcation", "status" => "success"], 200);
    }

    public function listUncompletedMamualPayment()
    {
        $page_number = request()->input("page_number");
        $payments = Payments::where("status", "2")->latest()->paginate($page_number);
        return response()->json(["message" => "Payments Confirmation list", "payments" => $payments, "status" => "success"], 200);
    }

}
