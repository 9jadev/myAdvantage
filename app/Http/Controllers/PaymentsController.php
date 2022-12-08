<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payments\ManualPaymentRequest;
use App\Models\Payments;
use PDF;

class PaymentsController extends Controller
{

    // public function adminlist() {
    //     $payments
    // }
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
        $type = request()->input("type");
        // return Payments::where("id", '20')->with('customer')->first();
        if ($type) {
            $payments = Payments::where("status", $type)->latest()->with('customer')->paginate($page_number);
            return response()->json(["message" => "Payments list", "payments" => $payments, "status" => "success", "res" => " 0 expecting payment 1 paid 2 manual payment"], 200);
        }
        $payments = Payments::latest()->with('customer')->paginate($page_number);
        return response()->json(["message" => "Payments list", "payments" => $payments, "status" => "success", "res" => " 0 expecting payment 1 paid 2 manual payment"], 200);
    }

    public function customerPaymentList()
    {
        $page_number = request()->input("page_number");
        $status = request()->input("status");
        $customer = auth()->user();
        $payments = Payments::where("customer_id", $customer->customer_id)->latest();
        if ($status) {
            $payments->where("status", $status);
        }
        $payments = $payments->paginate($page_number);
        return response()->json(["message" => "Payments list", "payments" => $payments, "status" => "success", "desc" => "status description 0 awating payment , 1 completed 2 canceled"], 200);
    }

    public function paymentInvoice()
    {
        $id = request()->input("id");
        $payment = Payments::where("id", $id)->first();
        if (!$payment) {
            return response()->json([
                "message" => "Payment doesn't exist.",
                "payments" => $payment,
                "status" => "error",
            ], 400);
        }

        if ($payment->status == 0) {
            return response()->json([
                "message" => "Payment not completed.",
                "payments" => $payment,
                "status" => "error",
            ], 400);
        }

        $data = [
            "id" => 12,
            "ref" => "qedw",
            "name" => "namw23",
        ];
        // return view("invoice");

        view()->share('invoice', $data);
        $pdf = PDF::loadView('invoice')->setPaper('A4')->stream();
        // download PDF file with download method
        return $pdf;
    }

}
