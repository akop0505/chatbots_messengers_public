<?php

namespace App\Http\Requests\UserRequest;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class StoreUserRequest extends FormRequest
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
    public function rules(Request  $request)
    {
        if($request->user()->hasRole('admin')){
            $parentIdValidation = 'nullable|exists:users,id,role_id,'.Role::select('id')->where(['name'=>'client'])->first()->id;
        }else{
            $parentIdValidation = 'nullable';
        }
        if($request->user()->hasRole('client')){
            $roleValidation = 'nullable|exists:roles,id';
        }
        else{
            $roleValidation = 'required|exists:roles,id';
        }
        return [
            'parent_id'=>$parentIdValidation,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'phone' => ['required','regex:/^\+[0-9]{11,12}$/'],
            'email' => 'required|email|unique:users',
            'username' => 'required|string|unique:users|min:5',
            'password' => 'required|min:6',
            'role_id'=>$roleValidation,
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        $validation = [];
        $validation["phone.regex"] =  "Вам необходимо заполнить телефон в формате +79099876543";
        $validation["email.unique"] =  "Такой email уже существует.";
        $validation["username.unique"] = "Такой логин уже существует.";
        return array_merge($validation,parent::messages()); // TODO: Change the autogenerated stub
    }

    /**
     * @param Validator $validator
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->messages(),
            'errorType'=>'VALIDATION_ERROR'
        ],422));
    }
}