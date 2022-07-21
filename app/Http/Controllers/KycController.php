<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customers\UpdateKycRequest;
use App\Models\Kyc;
use App\Services\MyIdService;
use Illuminate\Http\Request;

class KycController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(UpdateKycRequest $request)
    {
        $data = $request->validated();
        return $this->store($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($data)
    {
        $data = array_merge($data, [
            "customer_id" => auth()->user()->customer_id,
        ]);
        $kyc = Kyc::updateOrCreate($data);
        $kyc->customer;
        return response()->json([
            "status" => "success",
            "message" => "Created Successfully",
            "kyc" => $kyc,
        ], 200);
    }

    public function verifyBvn(Request $request)
    {
        // $request->validate(["bvn" => "required|string"]);
        if (!$request->bvn) {
            return response()->json(["message" => "BVN is required", "status" => "error"], 400);
        }
        $myId = MyIdService::verify($request->bvn);
        return response()->json($myId, $myId["status"] ? 200 : 400);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Kyc  $kyc
     * @return \Illuminate\Http\Response
     */
    public function show(Kyc $kyc)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Kyc  $kyc
     * @return \Illuminate\Http\Response
     */
    public function edit(Kyc $kyc)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Kyc  $kyc
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Kyc $kyc)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Kyc  $kyc
     * @return \Illuminate\Http\Response
     */
    public function destroy(Kyc $kyc)
    {
        //
    }
}
