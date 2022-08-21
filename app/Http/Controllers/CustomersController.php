<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customers\CreateCustomerRequest;
use App\Http\Requests\Customers\CreatePasswordRequest;
use App\Http\Requests\Customers\LoginCustomerRequest;
use App\Http\Requests\Customers\UpdateCustomerRequest;
use App\Http\Requests\Customers\UploadProfileRequest;
use App\Jobs\NewbiesJob;
use App\Mail\ForgotPassword;
use App\Models\Customers;
use App\Models\Kyc;
use App\Models\Payments;
use App\Models\Plans;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomersController extends Controller
{

    public function index()
    {
        $page_number = request()->input("page_number");
        $customers = Customers::latest()->select(["customer_id",
            "upliner",
            "referral_code",
            "firstname",
            "created_at",
            "phone_number",
            "lastname"])->paginate($page_number);
        return response()->json(["message" => "Customer list", "customer" => $customers, "status" => "success"], 200);
    }

    public function view()
    {
        if (request()->input("customer_id")) {

        }
    }

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
            "level" => "0",
            "upliner" => $data["referral_code"],
            "referral_code" => bin2hex(random_bytes(5)),
            'password' => bcrypt($data['password']),
        ]);
        $plan = Plans::where("id", $data["plan_id"])->first();
        $customers = Customers::create($data);
        $reference = Str::random(15);
        $payments = Payments::create(["reference" => $reference, "customer_id" => $customer_id, "amount" => $plan->plan_amount, "plan_id" => $data["plan_id"]]);
        $this->checkwallet($customers);
        $upliner = Customers::where("referral_code", $customers->upliner)->first();
        if ($upliner) {
            NewbiesJob::dispatch($upliner)->delay(now()->addMinutes(1));
        }
        return response()->json([
            "status" => "success",
            "message" => "Created Successfully",
            "reference" => $reference,
            "customers" => $customers,
            "plan" => $plan,
            'token' => $customers->createToken('mobile', ['role:customer'])->plainTextToken,
        ], 200);
    }

    public function generateWallet(Request $request)
    {
        $plan = Plans::where("id", $request->plan_id)->first();
        if ($plan == null) {
            return response()->json(["status" => "error", "message" => "Plan Id is required"], 400);
        }
        $reference = Str::random(15);
        $payments = Payments::create(["reference" => $reference, "customer_id" => auth()->user()->customer_id, "amount" => $plan->plan_amount, "plan_id" => $request->plan_id]);
        return response()->json(["payments" => $payments, "message" => "Payment done successfully"], 200);
    }

    private function updatepaymentSuccessful(Payments $payment)
    {
        // return $payment;
        $plan = Plans::where("id", $payment->plan_id)->first();
        $newDateTime = Carbon::now()->addDay($plan->pay_days);
        $customer = Customers::where("customer_id", $payment->customer_id)->first();
        $customer->next_pay = $newDateTime;
        $customer->save();
        $payment->status = '1';
        $payment->next_pay = $newDateTime;
        $payment->save();
        $paymentcount = Payments::where("status", "1")->count();
        if ($paymentcount == 1) {
            # code...
        }
        return response()->json([
            "message" => "Payment was successful",
            "status" => "success",
        ], 200);

    }

    public function confirmPayment(Request $request)
    {
        $request->validate(["ref" => 'required|string']);
        $manualpayments = Payments::where("reference", $request->ref)->first();
        // return $manualpayments;
        if (!$manualpayments) {
            return response()->json(["message" => "No payments", "status" => "error"], 400);
        }
        if ($manualpayments->status == "1") {
            return response()->json(["message" => "Payment already completed", "status" => "error"], 400);
        }
        $manualpayments->update(["status" => "1"]);
        $manualpayments->refresh();
        $this->updatepaymentSuccessful($manualpayments);
        return response()->json(["message" => "Verifcation success", "status" => "success"], 200);
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
            ])->get(env("FWAVE_BASE") . '/v3/transactions/verify_by_reference?tx_ref=' . $ref);
            $responseData = $response->json();
            // return $responseData["status"];
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
                    return $this->updatepaymentSuccessful($payment);
                } else {
                    // Inform the customer their payment was unsuccessful
                    return response()->json([
                        "message" => "Invalid transaction cco",
                        "payment" => $payment->amount,
                        "resp" => $responseData,
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
        if ($customer && !$customer->password) {
            return response()->json([
                "message" => "You neet to set a password",
                "status" => "error",
                "password" => "required.",
            ], 400);
        }
        if (!$customer || !Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        $customer->kyc;
        $this->checkwallet($customer);
        return response()->json([
            'customer' => $customer,
            "message" => "Customer Login Successfully.",
            'token' => $customer->createToken('mobile', ['role:customer'])->plainTextToken,
        ]);
    }

    private function checkwallet(Customers $customer)
    {
        $wallet = Wallet::updateOrCreate(["customer_id" => $customer->customer_id]);
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
        $customer->save();
        return response()->json([
            "message" => "Customer Profile Updated Successfully.",
            "customer" => auth()->user(),
            "status" => "success",
        ], 200);
    }

    public function viewOne($id)
    {
        $customers = Customers::where('id', $id)->first();
        if ($customers == null) {
            return response()->json([
                "message" => "Customer Doesn't exist.",
                "status" => "error",
            ], 400);
        }
        $customers->kyc;
        $customers->downliners = $customers->getDownliners($customers->referral_code);
        return response()->json([
            "message" => "Customer Fetched Successfully.",
            "status" => "success",
            "customer" => $customers,
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
        $customer->downliners = $customer->getDownliners($customers->referral_code);
        return response()->json([
            "message" => "Customer Fetched Successfully.",
            "status" => "success",
            "customers" => $customers,
        ], 200);
    }

    public function updateProfile(UpdateCustomerRequest $request)
    {
        $data = $request->validated();
        $customer = auth()->user();
        // return $customer;
        $customer->update($data);
        $customer->refresh();
        return response()->json([
            "message" => "Profile updated successfully.",
            "status" => "success",
            "customer" => $customer,
        ]);
        // $data = array_merge($data, ["customer" => $customer]);
        // return $data;
    }
}
