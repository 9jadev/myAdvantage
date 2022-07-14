<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customers\CreateCustomerRequest;
use App\Http\Requests\Customers\CreatePasswordRequest;
use App\Http\Requests\Customers\LoginCustomerRequest;
use App\Http\Requests\Customers\UploadProfileRequest;
use App\Mail\ForgotPassword;
use App\Models\Customers;
use App\Models\Payments;
use App\Models\Plans;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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

    public function forgotpassword()
    {
        $email = request()->input("email");
        if ($email == null) {
            return response()->json(["status" => "error", "message" => "Email is required."], 400);
        }
        $customer = Customers::where("email", $email)->first();
        if ($customer == null) {
            return response()->json(["status" => "error", "message" => "Email is'nt in our system."], 400);
        }
        $password = Str::random(10);
        $customer->password = bcrypt($password);
        $customer->save();
        try {
            Mail::to($email)->send(new ForgotPassword($password));
        } catch (\Throwable$th) {
            //log()->error($th);
        }

        return response()->json([
            "status" => "success",
            "message" => "Procced to your email for further insturction",
        ], 200);

    }

    public function addpassword(CreatePasswordRequest $request)
    {
        $data = $request->validated();
        $customer = Customers::where("email", $data["email"])->first();
        if ($customer->password != null) {
            return response()->json(["status" => "error", "message" => "Password already exist"], 400);
        }

        if ($customer == null) {
            return response()->json(["status" => "error", "message" => "Email is'nt in our system."], 400);
        }

        $customer->password = bcrypt($data["password"]);
        $customer->save();
        return response()->json(["status" => "success", "message" => "Password added successful"], 200);
    }

    public function create(CreateCustomerRequest $request)
    {
        $data = $request->validated();
        $checkplan = Plans::where("id", $data["plan_id"])->first();
        if (!$checkplan) {
            return response()->json(["status" => "error", "message" => "Invalid Plan"], 400);
        }
        return $this->store($data);
    }

    private function store($data)
    {
        $customer_id = "CUS_" . rand(10000, 99999) . date("YmdHis");
        $data = array_merge($data, [
            "status" => "0",
            "customer_id" => $customer_id,
            "referral_code" => bin2hex(random_bytes(5)),
            // 'password' => bcrypt($data['password']),
        ]);
        $plan = Plans::where("id", $data["plan_id"])->first();
        $customers = Customers::create($data);
        $reference = Str::random(15);
        $payments = Payments::create(["reference" => $reference, "customer_id" => $customer_id]);
        return response()->json([
            "status" => "success",
            "message" => "Created Successfully",
            "reference" => $reference,
            "customers" => $customers,
            "plan" => $plan,
            'token' => $customers->createToken('mobile', ['role:customer'])->plainTextToken,
        ], 200);
    }

    private function updatepaymentSuccessful(Payments $payment)
    {
        $plan = Plans::where("plan_id", $payment->plan_id)->first();
        $newDateTime = Carbon::now()->addDay($plan->pay_days);
        $customer = Customers::where("customer_id", $payment->customer_id)->first();
        $customer->next_pay = $newDateTime;
        $customer->save();
        $payment->status = '1';
        $payment->next_pay = $newDateTime;
        $payment->save();
        return response()->json([
            "message" => "Payment was successful",
            "status" => "success",
        ], 200);

    }

    public function verifyPayments()
    {
        $ref = request()->ref;
        if (!$ref) {
            return response()->json([
                "message" => "Reference is required",
                "status" => "error",
            ], 400);
        }
        $payment = Payments::where("reference", $ref)->first();
        if (!$payment) {
            return response()->json([
                "message" => "Reference doesn't exist within our system",
                "status" => "error",
            ], 400);
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " . env('FWAVE_PRIVATE_KEY'),
            ])->get('https://api.flutterwave.com/v3/transactions/' . $ref . '/verify');
            $responseData = $response->json();
            if ($responseData["status"] == "error") {
                return response()->json([
                    "message" => "Invalid transaction",
                    "status" => "error",
                ], 400);
            }

            if ($responseData["status"] == "success") {

                if (
                    $responseData['data']['status'] === "successful"
                    && $responseData['data']['amount'] === $payment->amount
                    && $responseData['data']['currency'] === "NGN") {
                    // Success! Confirm the customer's payment
                    $this->updatepaymentSuccessful($payment);
                } else {
                    // Inform the customer their payment was unsuccessful
                    return response()->json([
                        "message" => "Invalid transaction",
                        "status" => "error",
                    ], 400);

                }

                return response()->json([
                    "message" => "Invalid transaction",
                    "status" => "error",
                ], 400);
            }

        } catch (\Throwable$th) {
            throw $th;
        }

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
        $customer->kyc;
        return response()->json([
            'customer' => $customer,
            "message" => "Customer Login Successfully.",
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

    public function uploadProfile(UploadProfileRequest $request)
    {
        $data = $request->validated();
        $customer = auth()->user();
        $customer->photo_url = $data["photo_url"];
        return response()->json([
            "message" => "Customer Profile Updated Successfully.",
            "customer" => auth()->user(),
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
        $customers->kyc;
        return response()->json([
            "message" => "Customer Fetched Successfully.",
            "status" => "success",
            "customers" => $customers,
        ], 200);
    }
}
