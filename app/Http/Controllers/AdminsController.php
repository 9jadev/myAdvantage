<?php

namespace App\Http\Controllers;

use App\Models\Admins;
use App\Models\Walletlimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AdminsController extends Controller
{

    public function getWalletLimit()
    {
        $with = Walletlimit::first();
        return response()->json(["status" => "success", "limit" => $with, "message" => "Updated Successfully."], 400);
    }
    public function updateWalletLimit(Request $request)
    {
        if (!request()->input("max_top_up")) {
            return response()->json(["status" => "error", "message" => "Maximum Top Up Required."], 400);
        }
        if (!request()->input("max_withdrawal")) {
            return response()->json(["status" => "error", "message" => "Maximum Withdrawals."], 400);
        }
        $with = Walletlimit::first();
        $with->update(["max_top_up" => $request->max_top_up, "max_withdrawal" => $request->max_withdrawal]);
        $with->save();
        return response()->json(["status" => "success", "limit" => $with, "message" => "Updated Successfully."], 400);

    }
    public function showprofile()
    {
        $admin = Auth::user();
        return response()->json(["status" => "success", "admin" => $admin, "message" => "Admin profile has been fetched."], 200);
    }
    public function logout()
    {
        $user = request()->user(); //or Auth::user()
        // Revoke current user token
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        return response()->json([
            "message" => "Admin Logout Successfully.",
            "status" => "success",
        ], 200);

    }
    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required|string",
        ]);
        $admin = Admins::where('email', $request->email)->first();
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        return response()->json([
            'admin' => $admin,
            "message" => "Admin login successfully.",
            'token' => $admin->createToken('mobile', ['role:admin'])->plainTextToken,
        ]);
    }

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
            "firstname" => "required|string",
            "lastname" => "required|string",
            "phone_number" => "required|string",
            "email" => "required|email|unique:admins,email",
            "password" => "required|string|confirmed",
        ]);
        $emailcheck = Admins::where("email", "!=", $request->email)->where("phone_number", "!=", $request->phone_number)->first();
        if ($emailcheck) {
            return response()->json([
                "status" => "error",
                "message" => "Email and phone number already exist.",
            ], 400);
        }
        $data = $request->all();
        $data["password"] = bcrypt($data["password"]);
        $admin = Admins::create($data);
        return response()->json([
            "status" => "success",
            "admin" => $admin,
            "message" => "Admin created successfully.",
        ], 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Admins  $admins
     * @return \Illuminate\Http\Response
     */
    public function show(Admins $admins)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Admins  $admins
     * @return \Illuminate\Http\Response
     */
    public function edit(Admins $admins)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admins  $admins
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Admins $admins)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admins  $admins
     * @return \Illuminate\Http\Response
     */
    public function destroy(Admins $admins)
    {
        //
    }
}
