<?php

namespace App\Http\Controllers;

use App\Models\HelpCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\HelpCenterMail;

class HelpCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $helpCenters = HelpCenter::latest()->where("customer_id", auth()->user()->customer_id)->paginate(request()->input("page_number"));
        return response()->json([
            "message" => "Help Center Created Fetched.",
            "helpCenter" => $helpCenters,
            "status" => "success",
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            "title" => "required|string",
            "description" => "required|string"
        ]);

        $data = [
            "customer_id" => auth()->user()->customer_id,
            "title" => $request->title,
            "description" => $request->description
        ];
        $helpCenter = HelpCenter::create($data);
        try {
            Mail::to(config('mail.helpmail'))->send(new HelpCenterMail($helpCenter->title,$helpCenter->description,auth()->user()->email));
        } catch (\Throwable$th) {
            logs()->error($th);
        }
        return response()->json([
            "message" => "Help Center Created.",
            "helpCenter" => $helpCenter,
            "status" => "success",
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HelpCenter  $helpCenter
     * @return \Illuminate\Http\Response
     */
    public function show(HelpCenter $helpCenter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HelpCenter  $helpCenter
     * @return \Illuminate\Http\Response
     */
    public function edit(HelpCenter $helpCenter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HelpCenter  $helpCenter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HelpCenter $helpCenter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HelpCenter  $helpCenter
     * @return \Illuminate\Http\Response
     */
    public function destroy(HelpCenter $helpCenter)
    {
        //
    }
}
