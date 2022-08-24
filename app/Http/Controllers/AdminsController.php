<?php

namespace App\Http\Controllers;

use App\Models\Admins;
use App\Models\Customers;
use App\Models\Payments;
use App\Models\Transactions;
use App\Models\Walletlimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AdminsController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            "total_members" => $this->totalMember(),
            "total_income" => $this->totalIncome(),
            "total_payout" => $this->totalPayout(),
            "newbies_count" => $this->customerTypes(0),
            "starter_count" => $this->customerTypes(1),
            "rookie_count" => $this->customerTypes(2),
            "star_count" => $this->customerTypes(3),
            "bronze_count" => $this->customerTypes(4),
            "silver_count" => $this->customerTypes(5),
            "gold_count" => $this->customerTypes(6),
            "platinum_count" => $this->customerTypes(7),
        ], 200);
    }

    private function customerTypes($status)
    {
        return Customers::where('level', $status)->count();
    }
    private function totalPayout()
    {
        $transactions = Transactions::where("type", "debit")->sum("amount");
        return $transactions;
    }

    private function totalMember()
    {
        $customer = Customers::count();
        return $customer;
    }

    private function totalIncome()
    {
        $payments = Payments::where("status", '1')->sum("amount");
        return $payments;
    }

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
        return response()->json(["status" => "success", "limit" => $with, "message" => "Updated Successfully."], 200);

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
        $admins = Admins::latest()->paginate(request()->input("page_number"));
        return response()->json([
            "status" => "success",
            "message" => "Admin Fetched Successfully",
            "admins" => $admins,
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
            "firstname" => "required|string",
            "lastname" => "required|string",
            "phone_number" => "required|string",
            "admin_type" => "required|string",
            "email" => "required|email|unique:admins,email",
            "password" => "required|string|confirmed",
        ]);

        $admin = Admins::create([
            "firstname" => $request->firstname,
            "lastname" => $request->lastname,
            "phone_number" => $request->phone_number,
            "email" => $request->phone_number,
            "email" => $request->email,
            "password" => bcrypt($request->password),
        ]);
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
    public function show($id)
    {
        $admin = Admins::where("id", $id)->first();
        if ($admin == null) {
            return response()->json(["message" => "Admin doesn't exist.", "status" => "error", "admin" => $admin], 400);
        }
        return response()->json(["message" => "Admin fetched success.", "status" => "success", "admin" => $admin], 200);
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
    public function update(Request $request, $id)
    {
        $admin = Admins::where("id", $id)->first();
        if ($admin == null) {
            return response()->json(["message" => "Admin doesn't exist.", "status" => "error", "admin" => $admin], 400);
        }

        $request->validate([
            "firstname" => "required|string",
            "lastname" => "required|string",
            "phone_number" => "required|string",
            "admin_type" => "required|string",
            "email" => "required|email",
        ]);
        // return $admin;
        $checkmail = Admins::where("email", $request->email)->where("id", "!=", $id)->first();
        if ($checkmail) {
            return response()->json(["message" => "Admin email exist already.", "status" => "error", "admin" => $admin], 400);
        }
        $admin->update([
            "firstname" => $request->firstname,
            "lastname" => $request->lastname,
            "phone_number" => $request->phone_number,
            "admin_type" => $request->admin_type,
            "email" => $request->phone_number,
            "email" => $request->email,
        ]);
        $admin->save();

        return response()->json(["message" => "Admin updated successfully", "status" => "success", "admin" => $admin], 200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admins  $admins
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $admin = Admins::where("id", $id)->first();
        if ($admin == null) {
            return response()->json(["message" => "Admin doesn't exist.", "status" => "error", "admin" => $admin], 400);
        }
        $admin->delete();
        return response()->json(["message" => "Admin deleted successfully.", "status" => "success"], 400);

    }
}
