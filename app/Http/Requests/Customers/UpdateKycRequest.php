<?php

namespace App\Http\Requests\Customers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateKycRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "customer_id" => "nullable|string",
            "nationality" => "required|string",
            "state_of_residence" => "required|string",
            "house_address" => "required|string",
            "upliner" => "nullable|string",
            "community_interest" => "required|string",
            "future_aspiration" => "required|string",
            "discount_preferences" => "required|string",
            "pre_existing_health_condtion" => "required|string",
            "pre_existing_health_condtion_drug" => "required|string",
            "allergy" => "required|string",
            "next_of_kin_name" => "required|string",
            "next_of_kin_phone_number" => "required|string",
            "vbank_account_number" => "required|string",
            "pharmacy_location" => "required|string",
            "pharmacy_name" => "required|string",
        ];
    }

    /**
     * If validator fails return the exception in json form
     * @param Validator $validator
     * @return array
     */
    protected function failedValidation(Validator $validator)
    {
        $message = '';
        foreach ($validator->errors()->all() as $error) {
            $message .= "$error <br/> ";
        }
        $response = response()->json([
            'status' => 'error',
            'message' => $message,
        ], 422);

        throw (new ValidationException($validator, $response))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }

    public function failedAuthorization()
    {
        throw new AuthorizationException("You don't have the authority to perform this resource");
    }
}
