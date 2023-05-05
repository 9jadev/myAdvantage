<?php

namespace App\Http\Controllers;

use App\Http\Requests\Plans\CreatePlanRequest;
use App\Http\Requests\Plans\EditPlanRequest;
use App\Models\Claim;
use App\Models\ClaimPayment;
use App\Models\Plans;

class PlansController extends Controller
{
    public function index()
    {
        $plans = Plans::get();
        return response()->json([
            "status" => "success",
            "message" => "Plans Fetched Successfully",
            "plan" => $plans,
        ], 200);
    }

    public function create(CreatePlanRequest $request)
    {
        $data = $request->validated();
        return $this->store($data);
    }

    public function edit(EditPlanRequest $request)
    {
        $data = $request->validated();

        $plans = Plans::where("id", $data['plan_id'])->first();
        $plans->update($data);
        return response()->json([
            "status" => "success",
            "message" => "Plan Updated Successfully",
            "plan" => $plans,
        ], 200);

    }

    private function store($data)
    {
        $plan = Plans::create($data);
        foreach ($data["claim"] as $value) {
            $claim = Claim::where("id", $value)->first();
            $datavalue = [
                "claim_id" => $value,
                "plan_id" => $plan->id,
                "type" => $claim->type,
            ];
            ClaimPayment::create($datavalue);
        }
        return response()->json([
            "status" => "success",
            "message" => "Plan Created Successfully",
            "plan" => $plan,
        ], 200);
    }

    public function delete()
    {
        if (request("id") == null) {
            return response()->json([
                "status" => "error",
                "message" => "Id is required",
            ], 400);
        }
        $plan = Plans::where("id", request("id"))->first();
        if (!$plan) {
            return response()->json([
                "status" => "error",
                "message" => "Plan not found",
            ], 400);
        }
        $plan->delete();
        return response()->json([
            "status" => "success",
            "message" => "Plan deleted successfully",
        ], 200);
    }
}
