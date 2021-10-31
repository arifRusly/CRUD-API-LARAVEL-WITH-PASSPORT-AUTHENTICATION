<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Auth;

class EditUserPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::guard("api")->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'email' => 'required|string',
            'gender' => 'required|string',
            'department' => 'required|string',
            'state' => 'required|string'
        ];
    }

    protected function failedValidation(Validator $validator)
    {

         throw new HttpResponseException(
             response()->json([
                 'success' => false,
                 'message' => $validator->messages()->toArray()
             ],
                  JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
         );

    }
}
