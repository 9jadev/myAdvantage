<?php

namespace App\Http\Requests\Plans;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class CreatePlanRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'plan_name' => 'required|string',
            'tenor' => 'required|string',
            'plan_amount' => 'required|integer',
            'mlm' => 'nullable|boolean',
            'pay_days' => 'required|integer',
            'claim' => 'required|array',
            'claim.*' => 'required|integer|exists:claims,id',
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
