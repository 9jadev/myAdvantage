<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customers\SubmitDocumentRequest;
use App\Models\Customers;
use App\Models\Documents;

class DocumentsController extends Controller
{

    // status 0 awaiting , 1 rejected , 2 approved
    // type 1 bvn 2 idcard

    public function create(SubmitDocumentRequest $request)
    {
        $data = $request->validated();
        $customer = auth()->user();

        $data = array_merge($data, [
            "status" => "0",
            "customer_id" => $customer->customer_id,
        ]);

        return $this->store($data);
    }
    public function adminShow($id)
    {
        $documents = Documents::where('id', $id)->first();
        if (!$documents) {
            return response()->json([
                "message" => "Doesn't exist",
                "status" => "error",
            ], 400);
        }
        return response()->json([
            "message" => "Fetched successfully",
            "status" => "success",
            "documents" => $documents,
        ], 400);
    }

    public function mackGood($id)
    {
        $documents = Documents::where('id', $id)->where('type', "2")->first();
        if (!$documents) {
            return response()->json([
                "message" => "Doesn't exist",
                "status" => "error",
            ], 400);
        }

        $customer = Customers::where("customer_id", $documents->customer_id)->first();
        if ($documents->type == "1") {
            $customer->update([
                "bvn" => $documents->document_link,
            ]);
            $customer->save();
        }
        if ($documents->type == "2") {
            $customer->update([
                "id_document" => $documents->document_link,
            ]);
            $customer->save();
        }
        $documents->update([
            "status" => '2',
        ]);
        $documents->save();
        return response()->json([
            "message" => "Updated successfully",
            "status" => "success",
            "documents" => $documents,
        ], 400);

    }

    public function admminListDocument()
    {
        $status = request()->input('status');
        $type = request()->input('type');
        $documents = Documents::orderBy('id', 'DESC');
        if ($status != null) {
            $documents->where("status", $status);
        }
        if ($type != null) {
            $documents->where("type", $type);
        }
        $documents = $documents->paginate();
        return response()->json([
            "message" => "docuemnts listed",
            "status" => "success",
            "documents" => $documents,
        ], 400);

    }

    private function store($data)
    {
        $checkcustomer = Documents::where("customer_id", $data["customer_id"])->where("status", "0")->where("type", $data["type"])->first();
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
