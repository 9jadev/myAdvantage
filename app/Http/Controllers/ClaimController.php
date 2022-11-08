<?php

namespace App\Http\Controllers;

use App\Http\Requests\Claims\CreateClaims;
use App\Http\Requests\Claims\EditClaimRequest;
use App\Models\Claim;
use App\Models\ClaimAssignee;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $claim = Claim::latest()->paginate(request()->input("page_number"));
        $claim = Claim::latest();
        if (request()->input("type") != null) {
            $claim->where("type", request()->input("type"));
        }
        if (request()->input("id") != null) {
            $claim->where("id", request()->input("id"));
        }

        $claim = $claim->paginate(request()->input("page_number"));
        return response()->json([
            "status" => "success",
            "message" => "Claim Fetched Successfully",
            "claim" => $claim,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(CreateClaims $request)
    {
        $data = $request->validated();
        $claim = Claim::create($data);
        return response()->json(["message" => "Create Claim", "status" => "success", "claim" => $claim], 200);
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Claim  $claim
     * @return \Illuminate\Http\Response
     */
    public function show(Claim $claim)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Claim  $claim
     * @return \Illuminate\Http\Response
     */
    public function edit(EditClaimRequest $request)
    {
        $data = $request->validated();
        $claim = Claim::where("id", $data["id"])->first();
        $claim->updateOrCreate(
            ['id' => $data["id"]],
            $data
        );
        $claim->refresh();
        return response()->json([
            "message" => "Claim updated successfully",
            "status" => "success",
            "data" => $claim,
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Claim  $claim
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Claim $claim)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Claim  $claim
     * @return \Illuminate\Http\Response
     */
    public function destroy($claimid)
    {
        $claim = Claim::where("id", $claimid)->first();
        if (!$claim) {
            return response()->json([
                "message" => "Claim does not exist",
                "status" => "error",
            ]);
        }
        $claimassignee = ClaimAssignee::where("claim_id", $claimid)->count();
        if (!$claim || $claim->level != null) {
            return response()->json([
                "message" => "Claim does not exist",
                "status" => "error",
            ]);
        }

        if ($claimassignee != 0) {
            return response()->json([
                "message" => "Claim already assigned to member",
                "status" => "error",
            ]);
        }
        $claim->delete();
        return response()->json([
            "message" => "Claim deleted successfully",
            "status" => "success",
        ]);
    }
}
