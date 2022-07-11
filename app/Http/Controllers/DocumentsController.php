<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customers\SubmitDocumentRequest;
use App\Models\Documents;

class DocumentsController extends Controller
{

    //status 0 awaiting , 1 rejected , 2 approved

    public function create(SubmitDocumentRequest $request)
    {
        $data = $request->validated();
        return $this->store($data);
    }

    private function store($data)
    {
        $customer = auth()->user();

        $data = array_merge($data, [
            "status" => "0",
            "customer_id" => $customer->customer_id,
        ]);
        $checkcustomer = Documents::where("customer_id", $customer->customer_id)->where("status", "0")->first();
        if ($checkcustomer != null) {
            return response()->json([
                "status" => "error",
                "message" => "Document awaiting approvals",
            ], 400);

        }
        $customers = Documents::create($data);
        return response()->json([
            "status" => "success",
            "message" => "Created Successfully",
            "customers" => $customers,
        ], 200);
    }

}
