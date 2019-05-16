<?php

namespace App\Http\Requests\DialogRequest;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;


class DialogUploadFilesRequest extends FormRequest
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
        //dd(request()->all());
        return [
            "file"=>"required|file|max:10240|mimes:jpeg,bmp,png,doc,docx,zip,xls,xlsx,pdf,mpga,mp4,mov,avi,wav,mpeg,mpeg4,dejaview"
        ];
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
