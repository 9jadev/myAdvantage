<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customers\CreateCustomerRequest;
use App\Http\Requests\Customers\LoginCustomerRequest;
use App\Models\Customers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomersController extends Controller
{

    /**
     *  @OA\Post(
     *     path="/customers/register",
     *     operationId="register",
     *     tags={"Customer"},
     *     summary="Create new customers",
     *     description="Create new customers",
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="firstname",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="lastname",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="phone_number",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     type="string"
     *                 ),
     *
     *                 example={"firstname": "a3fb6", "lastname": "Jessica Smith", "email": "solomon.ahamba@botosoft.com" ,"phone_number": 09034426192, "password": "password", "password_confirmation": "password"}
     *             )
     *         )
     *     ),
     *   @OA\Response(
     *     response=200,
     *     description="A Customer Created Successfully"
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="A Customer Creation Validation Error"
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="A Customer Creation Error"
     *   ),
     *   @OA\Response(
     *     response=419,
     *     description="A Customer 419"
     *   ),
     *   @OA\Response(
     *     response=405,
     *     description="A Customer Creation Method Not Allowed"
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     *
     */

    public function create(CreateCustomerRequest $request)
    {
        $data = $request->validated();
        return $this->store($data);
    }

    private function store($data)
    {
        $data = array_merge($data, [
            "status" => "0",
            "customer_id" => "CUS_" . rand(10000, 99999) . date("YmdHis"),
            "referral_code" => bin2hex(random_bytes(10)),
            'password' => bcrypt($data['password']),
        ]);
        $customers = Customers::create($data);
        return response()->json([
            "status" => "success",
            "message" => "Created Successfully",
            "customers" => $customers,
        ], 200);
    }

    /**
     *  @OA\Post(
     *     path="/customers/login",
     *     operationId="login",
     *     tags={"Customer"},
     *     summary="Customer Login",
     *     description="Customer Login",
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *
     *                 example={"email": "solomon.ahamba@botosoft.com", "password": "password", }
     *             )
     *         )
     *     ),
     *   @OA\Response(
     *     response=200,
     *     description="A Customer Login Successfully"
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="A Customer Logining In Validation Error"
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="A Customer Logining Error"
     *   ),
     *   @OA\Response(
     *     response=419,
     *     description="A Customer 419"
     *   ),
     *   @OA\Response(
     *     response=405,
     *     description="A Customer Creation Method Not Allowed"
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     *
     */

    public function login(LoginCustomerRequest $request)
    {
        $data = $request->validated();
        $customer = Customers::where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'customer' => $customer,
            'token' => $customer->createToken('mobile', ['role:customer'])->plainTextToken,
        ]);
    }

    /**
     * @OA\Get(
     *  path="/customers/profile",
     *  operationId="profile",
     *  tags={"Customer"},
     *  summary="Get the list of resources",
     *  @OA\Response(response=200, description="Return a list of resources"),
     *  @OA\Response(response=401, description="Authorization required."),
     *  @OA\Response(response=500, description="Server Error."),
     *  security={{"bearer_token":{}}},
     * )
     */

    public function logout()
    {
        $user = request()->user(); //or Auth::user()
        // Revoke current user token
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        return response()->json([
            "message" => "Customer Logout Successfully.",
            "status" => "success",
        ], 200);

    }
    public function getData()
    {
        $id = Auth::id();
        $customer = Customers::where('id', $id)->first();
        if ($customer == null) {
            return response()->json([
                "message" => "Customer Doesn't exist.",
                "status" => "error",
            ], 400);
        }
        $customers = Auth::user();
        return response()->json([
            "message" => "Customer Fetched Successfully.",
            "status" => "success",
            "customers" => $customers,
        ], 200);
    }
}
