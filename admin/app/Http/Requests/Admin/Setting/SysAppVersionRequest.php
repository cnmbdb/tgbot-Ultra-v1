<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class SysAppVersionRequest extends FormRequest
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
            'version' => 'integer',
            'apkurl' => 'required',
            'is_upgrade_force' => 'required',
            'is_download_force' => 'required',
            'hot_upgrade_url' => 'required',
            'upgrade_comments' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'version.integer' => '版本号必须是数字',
            'apkurl.required' => '下载地址不能为空',
            'is_upgrade_force.required' => '是否强制不能为空',
            'is_download_force.required' => '是否需要下载不能为空',
            'hot_upgrade_url.required' => '热更新下载地址不能为空',
            'upgrade_comments.required' => '更新内容不能为空',
        ];
    }
}
