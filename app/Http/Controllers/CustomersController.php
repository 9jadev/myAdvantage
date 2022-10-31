<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customers\CreateCustomerRequest;
use App\Http\Requests\Customers\CreatePasswordRequest;
use App\Http\Requests\Customers\LoginCustomerRequest;
use App\Http\Requests\Customers\UpdateCustomerRequest;
use App\Http\Requests\Customers\UploadProfileRequest;
use App\Jobs\NewbiesJob;
use App\Mail\ForgotPassword;
use App\Models\Claim;
use App\Models\ClaimAssignee;
use App\Models\ClaimPayment;
use App\Models\Customers;
use App\Models\Kyc;
use App\Models\Payments;
use App\Models\Plans;
use App\Models\Wallet;
use App\Notifications\AssignClaimNotify;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomersController extends Controller
{

    public function listNotification()
    {
        $notification = auth()->user()->unreadNotifications()->get();
        return response()->json(["message" => "Notification list", "notification" => $notification, "status" => "success"], 200);
    }

    public function markAllRead()
    {
        $notification = auth()->user()->unreadNotifications->markAsRead();
        return response()->json(["message" => "Notification marked as read", "notification" => $notification, "status" => "success"], 200);
    }

    public function markRead()
    {
        $id = request()->input("id");
        if ($id == null) {
            return response()->json(["message" => "Id is required", "status" => "error"], 400);
        }
        $notification = auth()->user()->unreadNotifications()->where("id", $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(["message" => "Marked as read.", "notification" => $notification, "status" => "success"], 200);
    }

    public function index()
    {
        $page_number = request()->input("page_number");
        $customers = Customers::latest()->select(["customer_id",
            "upliner",
            "id",
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
            Log::error($th);
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
        $claims = ClaimPayment::where("plan_id", $payment->plan_id)->get();
        // return $claims;
        foreach ($claims as $value) {
            $dd = [
                "customer_id" => $payment->customer_id,
                "claim_id" => $value["id"],
                "status" => 0,
                "type" => $value->claim->type,
            ];
            ClaimAssignee::create($dd);
            // event(new AssignClaimEvent(auth()->user()->customer_id, $dd["claim_id"]));

            $claim = Claim::where("id", $value["id"])->first();

            auth()->user()->notify((new AssignClaimNotify($claim, auth()->user()))->delay([
                'mail' => now()->addMinutes(2),
                'sms' => now()->addMinutes(3),
            ]));

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
            return response()->json(["message" => "Payment Already Verified", "status" => "error"], 400);
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

        if ($payment->status == '1') {
            return response()->json(["message" => "Payment Already Verified", "status" => "error"], 400);

        }

        // return $payment;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " . env('FWAVE_PRIVATE_KEY'),
            ])->get(env("FWAVE_BASE") . '/transactions/verify_by_reference?tx_ref=' . $ref);
            $responseData = $response->json();
            // return 12324;
            // return $responseData;
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

    public function changePassword()
    {
        $oldp = request()->input("oldpassword");
        $newp = request()->input("newpassword");
        if ($oldp == null) {
            return response()->json(["status" => "error", "message" => "Old password is required."], 400);
        }
        if ($newp == null) {
            return response()->json(["status" => "error", "message" => "New password is required."], 400);
        }
        $customer = Customers::where("id", auth()->user()->id)->first();
        if (!$customer || !Hash::check($oldp, $customer->password)) {
            return response()->json(["status" => "error", "message" => "Old password is not correct."], 400);
        }
        $customer->password = bcrypt($newp);
        $customer->save();
        return response()->json(["status" => "success", "message" => "Password has been changed."], 200);
    }

    public function updateProfile1(UpdateCustomerRequest $request)
    {

        $data = $request->validated();
        // return $data;
        if (!isset($data["customer_id"])) {
            return response()->json(["status" => "error", "message" => "Customer Id Required"], 400);
        }

        $customer = Customers::where("customer_id", $data['customer_id'])->first();
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

    public function loaDowlainers()
    {
        $referral_code = auth()->user()->referral_code;
        // return auth()->user();
        $data = [];

        $downliners = Customers::where("upliner", $referral_code)->get(["firstname", "lastname", "referral_code"]);
        $level2data = $downliners->pluck("referral_code")->all();
        // return $level2data;
        $level1 = [
            "level" => "1",
            "data" => $downliners,
        ];

        $level2 = $this->level2($level2data);

        $level3data = $level2["data"]->pluck("referral_code")->all();
        // return $level3data;

        $level3 = $this->level3($level3data);
        $level4data = $level3["data"]->pluck("referral_code")->all();

        $level4 = $this->level4($level4data);
        $level5data = $level4["data"]->pluck("referral_code")->all();

        $level5 = $this->level5($level5data);
        $level6data = $level5["data"]->pluck("referral_code")->all();

        $level6 = $this->level6($level6data);
        $level7data = $level6["data"]->pluck("referral_code")->all();

        $level7 = $this->level7($level7data);
        $level8data = $level7["data"]->pluck("referral_code")->all();

        $level8 = $this->level8($level8data);
        $level9data = $level7["data"]->pluck("referral_code")->all();

        $level9 = $this->level9($level9data);

        $data[] = $level1;
        $data[] = $level2;
        $data[] = $level3;
        $data[] = $level4;
        $data[] = $level5;
        $data[] = $level6;
        $data[] = $level7;
        $data[] = $level8;
        $data[] = $level9;
        // return $data;
        return response()->json([
            "message" => "Tree Generology fetched",
            "status" => "error",
            "data" => $data,
        ], 200);
    }

    private function level2($level2data)
    {
        $downliners = Customers::whereIn("upliner", $level2data)->get(["firstname", "lastname", "referral_code"]);
        $level2 = [
            "level" => "2",
            "data" => $downliners,
        ];
        return $level2;
    }

    private function level3($level3data)
    {
        $downliners = Customers::whereIn("upliner", $level3data)->get(["firstname", "lastname", "referral_code"]);
        $level3 = [
            "level" => "3",
            "data" => $downliners,
        ];
        return $level3;
    }

    private function level4($level4data)
    {
        $downliners = Customers::whereIn("upliner", $level4data)->get(["firstname", "lastname", "referral_code"]);
        $level4 = [
            "level" => "4",
            "data" => $downliners,
        ];
        return $level4;
    }

    private function level5($level5data)
    {
        $downliners = Customers::whereIn("upliner", $level5data)->get(["firstname", "lastname", "referral_code"]);
        $level5 = [
            "level" => "5",
            "data" => $downliners,
        ];
        return $level5;
    }

    private function level6($level6data)
    {
        $downliners = Customers::whereIn("upliner", $level6data)->get(["firstname", "lastname", "referral_code"]);
        $level6 = [
            "level" => "6",
            "data" => $downliners,
        ];
        return $level6;
    }

    private function level7($level7data)
    {
        $downliners = Customers::whereIn("upliner", $level7data)->get(["firstname", "lastname", "referral_code"]);
        $level7 = [
            "level" => "7",
            "data" => $downliners,
        ];
        return $level7;
    }

    private function level8($level8data)
    {
        $downliners = Customers::whereIn("upliner", $level8data)->get(["firstname", "lastname", "referral_code"]);
        $level8 = [
            "level" => "8",
            "data" => $downliners,
        ];
        return $level8;
    }

    private function level9($level9data)
    {
        $downliners = Customers::whereIn("upliner", $level9data)->get(["firstname", "lastname", "referral_code"]);
        $level9 = [
            "level" => "9",
            "data" => $downliners,
        ];
        return $level9;
    }
}
