<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MyIdService
{
    // Declare the function as static
    public static function verify(string $slug)
    {

        try {
            $response = Http::withHeaders([
                'x-api-key' => env("ID_PASS_SECRET"),
                'X-Second' => env('ID_APP_ID'),
            ])->post(env("ID_BASE_URL") . '/api/v2/biometrics/merchant/data/verification/bvn', [
                'number' => $slug,
            ]);
            $resData = $response->json();
            if ($resData["status"] == true) {
                $resData["message"] = "Verified successfully";
                return $resData;
            } else {
                $resData["message"] = "Error Occoured";
                return $resData;
            }

        } catch (\Throwable$th) {
            Log::error($th);
            $resData["message"] = "Error Occoured";
            return $resData;

        }

    }
}
