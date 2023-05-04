<?php

namespace App\Services;

use App\Models\Transactions;
use App\Models\Wallet;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WalletService
{

    public function verifyPayments()
    {
        $ref = request()->ref;
        if (!$ref) {
            return response()->json([
                "message" => "Reference is required",
                "status" => "error",
            ], 400);
        }
        $payment = Transactions::where("reference", $ref)->first();
        if (!$payment) {
            return response()->json([
                "message" => "Reference doesn't exist within our system",
                "status" => "error",
            ], 400);
        }

        if ($payment->status == "1") {
            return response()->json([
                "message" => "Transaction already completed.",
                "status" => "error",
            ], 400);
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " . env('FWAVE_PRIVATE_KEY'),
            ])->get(env('FWAVE_BASE') . '/transactions/verify_by_reference?tx_ref=' . $ref);
            $responseData = $response->json();
            // return $responseData;
            if ($responseData["status"] == "error") {
                return response()->json([
                    "message" => "Invalid transaction",
                    "status" => "error",
                ], 400);
            }

            if ($responseData["status"] == "success") {

                if (
                    $responseData['data']['status'] === "successful"
                    && $responseData['data']['amount'] === $payment->amount
                    && $responseData['data']['currency'] === "NGN") {
                    // Success! Confirm the customer's payment
                    return $this->updatepaymentSuccessful($payment);
                } else {
                    // Inform the customer their payment was unsuccessful
                    return response()->json([
                        "message" => "Invalid transaction",
                        "payment" => $payment->amount,
                        "resp" => $responseData,
                        "status" => "error",
                    ], 400);
                }

                return response()->json([
                    "message" => "Invalid transaction",
                    "status" => "error",
                ], 400);
            }

        } catch (\Throwable$th) {
            throw $th;
        }

    }



    private function updatepaymentSuccessful(Transactions $transactions)
    {
        // return $payment;
        $transactions->update(["status" => 1]);
        $wallet = Wallet::where("customer_id", $transactions->customer_id)->first();
        $balance = $wallet->balance + $transactions->amount;
        $wallet->update([
            "balance" => $balance,
        ]);
        $wallet->save();

        return response()->json([
            "message" => "Payment was successful",
            "status" => "success",
        ], 200);

    }
}
