<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customers\SubmitDocumentRequest;
use App\Models\Customers;
use App\Models\Documents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentsController extends Controller
{

    // status 0 awaiting , 1 rejected , 2 approved
    // type 0 bvn 1 idcard

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
        $admin = Auth::user();
        $documents = Documents::where('id', $id)->where('status', "0")->first();
        if (!$documents) {
            return response()->json([
                "message" => "Doesn't exist",
                "status" => "error",
            ], 400);
        }

        $customer = Customers::where("customer_id", $documents->customer_id)->first();
        if ($documents->type == "0") {
            $customer->update([
                "bvn" => $documents->document_link,
            ]);
            $customer->save();
        }
        if ($documents->type == "1") {
            $customer->update([
                "id_document" => $documents->document_link,
            ]);
            $customer->save();
        }
        $documents->update([
            "status" => '2',
            "admin_id" => $admin->id,
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
                "awaiting" => true,
                "message" => "Document awaiting approvals",
            ], 200);

        }
        $customers = Documents::create($data);
        return response()->json([
            "status" => "success",
            "message" => "Created Successfully",
            "awaiting" => false,
            "customers" => $customers,
        ], 200);
    }

}
