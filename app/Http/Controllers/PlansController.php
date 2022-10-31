<?php

namespace App\Http\Controllers;

use App\Http\Requests\Plans\CreatePlanRequest;
use App\Http\Requests\Plans\EditPlanRequest;
use App\Models\ClaimPayment;
use App\Models\Plans;
use App\Models\Claim;

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
            $claim = Claim::where("id", $value["id"])->first();
            $datavalue = [
                "claim_id" => $value,
                "plan_id" => $plan->id,
                "type" => $claim->type
            ];
            ClaimPayment::create($datavalue);
        }
        return response()->json([
            "status" => "success",
            "message" => "Plan Created Successfully",
            "plan" => $plan,
        ], 200);
    }
}
