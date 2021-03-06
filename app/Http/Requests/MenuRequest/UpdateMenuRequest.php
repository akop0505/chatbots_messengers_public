<?php

namespace App\Http\Requests\MenuRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;

class UpdateMenuRequest extends FormRequest
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
        $validation = [
            'project_id'=>'exists:projects,id',
            'name'=>'required|string|min:6|max:255',
            'callback_url'=>'regex:/^(https?:\/\/)?([\da-z\.\:-]+)[\.\/]([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
            'MenuItems'=>'required|array',
            'MenuItems.*.name'=>'required|string|min:1|max:255',
            'MenuItems.*.point'=>['required','regex:/^[\w\d]+$/'],
            'MenuItems.*.callback_url'=>['nullable','regex:/^(https?:\/\/)?([\da-z\.\:-]+)[\.\/]([a-z\.]{2,6})([\/\w \.-]*)*\/?$/'],

            'PresentReplies.*.point'=>'required|string|min:1|max:255',
            'PresentReplies.*.text'=>'required|string|min:6|max:255',
        ];
        $menuItems = $request->get('MenuItems');
        if($menuItems){
            foreach ($menuItems as $key=>$menuItem){
                if($menuItem['auto_reply'] && $menuItem['reply_type'] === "template"){
                    $validation['MenuItems.'.$key.'.callback_url'] = ['required','regex:/^(https?:\/\/)?([\da-z\.\:-]+)[\.\/]([a-z\.]{2,6})([\/\w \.-]*)*\/?$/'];
                }
            }
        }

        return $validation;
    }

    /**
     * @return array
     */
    public function messages()
    {
        $validation = [
            "callback_url.regex"=>"Вам необходимо заполнить функцию обратного вызова в формате http://www.example.com",
            "MenuItems.required"=>"Не было создано ни одного варианта",
            "MenuItems.*.point.regex"=>"Поле должно содержать только буквы или цифры.",
        ];
        for($i = 0; $i <= count(request()->get('MenuItems')); $i++){
            $validation['MenuItems.'.$i.'.callback_url.regex'] = "Вам необходимо заполнить функцию обратного вызова в формате http://www.example.com";
        }
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
