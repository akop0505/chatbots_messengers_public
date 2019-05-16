<?php

namespace App\Http\Requests\ProjectRequest;

use App\Models\Gateway;
use App\Models\GatewaySetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;

class UpdateProjectRequest extends FormRequest
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
        $rules = [
            'name'=>'required|string|min:6|max:255',
            'web_site'=>['required','regex:/^(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/'],
//            'web_site'=>['required','regex:/^(https?:\/\/)?([\da-z\.\:-]+)[\.\/]([a-z\.]{2,6})([\/\w \.-]*)*(#)?([\/\w \.-]*)*\/?$/'],
            'client_id'=>$request->user()->hasRole('client') ? 'nullable|exists:users,id' : 'required|exists:users,id',
            'ProjectMessengers'=>'nullable|array',
            'ProjectMessengers.*.gateway_id'=>'exists:gateways,id|nullable',
            'ProjectMessengers.*.permission_id'=>'exists:permissions,id|nullable'
        ];

        $projectMessengers = $request->get('ProjectMessengers');

        foreach ($projectMessengers as $key=>$pm) {
            //validate gateway fields
            if($pm['gateway_id']){
                $pmRule = [
                    'ProjectMessengers.'.$key.'.bot_username'=>['nullable','regex:/^[\Sa-zA-Z0-9]*$/'],
                    'ProjectMessengers.'.$key.'.bot_name'=>'string|max:255|nullable',
                    'ProjectMessengers.'.$key.'.phone'=>['nullable','regex:/^\+[0-9]{11,12}$/'],
                    'ProjectMessengers.'.$key.'.gateway_token'=>'nullable|min:6|string',
                    'ProjectMessengers.'.$key.'.bot_token'=>'string|nullable',

                ];
                $gateway = Gateway::select('config')->find($pm['gateway_id']);
                $gatewayConfig = json_decode($gateway);

                $gatewaySettings = $pm['GatewaySettings'];
                foreach ($gatewaySettings as $index=>$gatewaySetting){
                    $configKey = array_search($gatewaySetting['field_name'], array_column($gatewayConfig->config, 'field_name'));
                    if($configKey !== false){
                        $pmRule['ProjectMessengers.'.$key.'.GatewaySettings.'.$index. '.field_name'] = 'required|string';
                        if(!empty($gatewayConfig->config[$configKey]->validation)){
                            $pmRule['ProjectMessengers.'.$key.'.GatewaySettings.'.$index. '.field_value'] = ['regex:'.$gatewayConfig->config[$configKey]->validation];
                        }                    }
                }
            } else {
                //validate bots fields
                $pmRule = [
                    'ProjectMessengers.'.$key.'.bot_username'=>['required','regex:/^[\Sa-zA-Z0-9]*$/'],
                    'ProjectMessengers.'.$key.'.bot_name'=>'required|string|min:6|max:255',
                    'ProjectMessengers.'.$key.'.phone'=>['nullable','regex:/^\+[0-9]{11,12}$/'],
                    'ProjectMessengers.'.$key.'.gateway_token'=>'string|nullable',
                    'ProjectMessengers.'.$key.'.bot_token'=>'required|min:6|string',
                ];
            }
            $rules = array_merge($rules,$pmRule);
        }
        return $rules;
    }

    /**
     * @return array
     */
    public function messages()
    {
        $validation = [];
        $projectMessengers = request()->get('ProjectMessengers');
        for($i = 0; $i < count(request()->get('ProjectMessengers')); $i++){
            $validation['ProjectMessengers.'.$i.'.phone.regex'] = 'Вам необходимо заполнить телефон в формате +79099876543';
            $validation['ProjectMessengers.'.$i.'.bot_username.regex'] = 'Вам необходимо заполнить username  без пробелов';
            if($projectMessengers[$i]['gateway_id']){
                $gateway = Gateway::select('config')->find($projectMessengers[$i]['gateway_id']);
                $gatewayConfig = json_decode($gateway);
                $gatewaySettings = $projectMessengers[$i]['GatewaySettings'];
                foreach ($gatewaySettings as $index=>$gatewaySetting){
                    $configKey = array_search($gatewaySetting['field_name'], array_column($gatewayConfig->config, 'field_name'));
                    if($configKey !== false){
                        $validation['ProjectMessengers.'.$i.'.GatewaySettings.'.$index.'.field_value.regex'] = $gatewayConfig->config[$configKey]->message;
                    }
                }
            }
        }
        $validation["web_site.regex"] =  ["Вам необходимо заполнить URL сайта в формате http://www.example.com"];
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
