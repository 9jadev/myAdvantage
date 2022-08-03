<?php

namespace App\Services;

use App\Models\Transactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreateTransaction
{
    public function generateTransfer(Transactions $transactions)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " . env('FWAVE_PRIVATE_KEY'),
            ])->post(env('FWAVE_BASE') . "/transfers", '{
                "account_bank" : ' . $transactions->bank_account_code . ',
                "account_number": ' . $transactions->bank_account_number . ',
                "amount" : ' . $transactions->amount . ',
                "narration": ' . $transactions->message . ',
                "currency": "NGN",
                "reference" : ' . $transactions->reference . ',
                "debit_currency" : "NGN",
		}');
            $responseData = $response->json();
            Log::error($responseData);
            if ($responseData["status"] == "success") {
                $transactions->update(["status" => 1]);
                $transactions->save();
                return response()->json([
                    "message" => "Bank List Fetched Successfully",
                    "status" => "success",
                    "fw_response" => $responseData,
                ], 200);
            } else {
                Log::error($responseData);

                return response()->json([
                    "message" => $responseData["message"],
                    "status" => "error",
                ], 200);
            }
        } catch (\Throwable$th) {
            Log::error($th);
            return response()->json([
                "message" => "Error Occoured",
                "status" => "error",
            ], 200);
        }

    }
}
