<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class SysDataDictionaryRequest extends FormRequest
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
        return [
            'dic_key' => 'required',
            'dic_value' => 'required',
            'dic_name' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'dic_key.required' => '字典key不能为空',
            'dic_value.required' => '字典value不能为空',
            'dic_name.required' => '字典显示描述不能为空',
        ];
    }
}
