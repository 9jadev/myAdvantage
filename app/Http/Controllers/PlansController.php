<?php

namespace App\Http\Controllers;

use App\Http\Requests\Plans\CreatePlanRequest;
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

    private function store($data)
    {
        $plan = Plans::create($data);
        return response()->json([
            "status" => "success",
            "message" => "Plan Created Successfully",
            "plan" => $plan,
        ], 200);
    }
}
