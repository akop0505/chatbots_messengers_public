<?php

namespace App\Http\Requests\EventRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class UpdateEventsRequest extends FormRequest
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
    public function rules(Request $request)
    {
        $id = $request->route('event');
        return [
            "project_id"=>"required|exists:projects,id",
//            "name"=>"required|string|min:6|max:255".Rule::unique('users')->ignore($id,'id'),
            "name"=>"required|string|min:6|max:255|unique:events,name,{$id},id,deleted_at,NULL",
            "description"=>"nullable|string|min:20|max:255",
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return parent::messages(); // TODO: Change the autogenerated stub
    }

    /**
     * @param Validator $validator
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->messages(),
            'errorType' => 'VALIDATION_ERROR'
        ], 422));
    }
}