<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transactions\CreateTransferRequest;
use App\Models\Transactions;
use App\Models\Wallet;
use App\Services\CreateTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page_number = request()->input("page_number");
        $payments = Transactions::where("status", request()->input("status"))->latest()->paginate($page_number);
        return response()->json(["message" => "Transactions list", "payments" => $payments, "status" => "success"], 200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!$request->amount) {
            return response()->json(["message" => "Amount is required"]);
        }
        return $this->store($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $customer = auth()->user();
        $reference = Str::random(15);
        $transactions = Transactions::create(["customer_id" => $customer->customer_id, "amount" => $request->amount, "status" => 0, "type" => "credit", "message" => "Fund Account", "reference" => $reference]);
        return response()->json(["Created Successfully", "status" => "success", "transactions" => $transactions]);
    }

    public function listBanks()
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " . env('FWAVE_PRIVATE_KEY'),
            ])->get(env('FWAVE_BASE') . "/banks/NG");
            $responseData = $response->json();
            if ($responseData["status"] == "success") {
                return response()->json([
                    "message" => "Bank List Fetched Successfully",
                    "status" => "success",
                    "banks" => $responseData["data"],
                ], 200);
            } else {
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

    public function createTransfer(CreateTransferRequest $request, CreateTransaction $createtransaction)
    {
        $customer = auth()->user();
        $reference = Str::random(15);
        $transaction = Transactions::create(["customer_id" => $customer->customer_id, "amount" => $request->amount, "status" => 0, "type" => "debit", "message" => "Withdraw Fund", "reference" => $reference, "bank_account_name" => $request->bank_account_name, "bank_account_number" => $request->bank_account_number, "bank_account_code" => $request->bank_account_code]);
        return $createtransaction->generateTransfer($transaction);

    }

    public function paymentWebhook(Request $request)
    {
        // Log::error($request);
        return $request;
    }

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

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " . env('FWAVE_PRIVATE_KEY'),
            ])->get(env('FWAVE_BASE') . '/transactions/verify_by_reference?tx_ref=' . $ref);
            $responseData = $response->json();
            return $responseData;
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
                        "message" => "Invalid transaction cco",
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transactions  $transactions
     * @return \Illuminate\Http\Response
     */
    public function show(Transactions $transactions)
    {
        return response()->json(["Fetched Successfully", "status" => "success", "transactions" => $transactions]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transactions  $transactions
     * @return \Illuminate\Http\Response
     */
    public function edit(Transactions $transactions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transactions  $transactions
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transactions $transactions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transactions  $transactions
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transactions $transactions)
    {
        //
    }
}
