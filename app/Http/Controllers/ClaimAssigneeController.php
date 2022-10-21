<?php

namespace App\Http\Controllers;

use App\Events\AssignClaim as AssignClaimEvent;
use App\Http\Requests\Claims\AssignClaimRequest;
use App\Models\ClaimAssignee;
use Illuminate\Http\Request;

class ClaimAssigneeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $claim = ClaimAssignee::latest()->paginate(request()->input("page_number"));
        $claim = ClaimAssignee::latest();
        if (request()->input("status") != null) {
            $claim->where("status", request()->input("status"));
        }
        if (request()->input("claim_id") != null) {
            $claim->where("claim_id", request()->input("claim_id"));
        }
        if (request()->input("customer_id") != null) {
            $claim->where("customer_id", request()->input("customer_id"));
        }
        if (request()->input("id") != null) {
            $claim->where("id", request()->input("id"));
        }

        if (request()->input("type") != null) {
            $claim->where("type", request()->input("type"));
        }

        $claim = $claim->paginate(request()->input("page_number"));
        return response()->json([
            "status" => "success",
            "message" => "Claim Assignment Fetched Successfully",
            "claim" => $claim,
        ], 200);

    }

    public function indexCustomer()
    {
        // $claim = ClaimAssignee::latest()->paginate(request()->input("page_number"));
        $claim = ClaimAssignee::latest();
        if (request()->input("status") != null) {
            $claim->where("status", request()->input("status"));
        }
        if (request()->input("claim_id") != null) {
            $claim->where("claim_id", request()->input("claim_id"));
        }
        if (request()->input("type") != null) {
            $claim->where("type", request()->input("type"));
        }

        if (request()->input("id") != null) {
            $claim->where("id", request()->input("id"));
        }

        $claim = $claim->where("customer_id", auth()->user()->customer_id)->paginate(request()->input("page_number"));
        return response()->json([
            "status" => "success",
            "message" => "Claim Assignment Fetched Successfully",

        ], 200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(AssignClaimRequest $request)
    {
        $data = $request->validated();
        // $createAssign = ClaimAssignee::create($data);
        event(new AssignClaimEvent($data["customer_id"], $data["claim_id"]));
        return response()->json(["message" => "Assignment created successfully", "status" => "success"], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    public function changeStatusAdmin()
    {

        if (request()->input("id") == null) {
            return response()->json(["message" => "Id is required.", "status" => "error"], 400);
        }

        if (request()->input("status") == null) {
            return response()->json(["message" => "Status is required.", "status" => "error"], 400);
        }

        $claim = ClaimAssignee::where("id", request()->input("id"))->first();
        // return $claim;
        if (!$claim) {
            return response()->json(["message" => "Claim doesn't exist.", "status" => "error"], 400);
        }

        $claim->update(["status" => request()->input("status")]);
        return response()->json(["message" => "Claim updated.", "status" => "success"], 200);
    }

    public function changeStatusCustomer()
    {
        if (request()->input("id") == null) {
            return response()->json(["message" => "Id is required.", "status" => "error"], 400);
        }

        if (request()->input("status") == null) {
            return response()->json(["message" => "Status is required.", "status" => "error"], 400);
        }
        // return auth()->user();
        // return auth()->user()->phone_number;

        $claim = ClaimAssignee::where("id", request()->input("id"))->where('customer_id', auth()->user()->customer_id)->first();
        // return $claim;
        if (!$claim) {
            return response()->json(["message" => "Claim doesn't exist.", "status" => "error"], 400);
        }

        $claim->update(["status" => request()->input("status")]);
        return response()->json(["message" => "Claim updated.", "status" => "success"], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ClaimAssignee  $claimAssignee
     * @return \Illuminate\Http\Response
     */
    public function show(ClaimAssignee $claimAssignee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ClaimAssignee  $claimAssignee
     * @return \Illuminate\Http\Response
     */
    public function edit(ClaimAssignee $claimAssignee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ClaimAssignee  $claimAssignee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClaimAssignee $claimAssignee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ClaimAssignee  $claimAssignee
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClaimAssignee $claimAssignee)
    {
        //
    }
}
