<?php

namespace App\Http\Controllers\Admin\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\Test;
use App\Models\System\SysConfig;

class HomeController extends Controller
{
    public function index()
    {
        // 检查系统是否已激活授权
        $licenseConfig = SysConfig::where('config_key', 'license_activation')->first();
        $isLicensed = false;
        
        if ($licenseConfig) {
            $configVal = $licenseConfig->config_val;
            $localLicense = is_string($configVal) ? json_decode($configVal, true) : (array) $configVal;
            if (!empty($localLicense['auth_code'])) {
                $isLicensed = true;
            }
        }
        
        return view('admin.home.index', [
            'is_licensed' => $isLicensed,
            'show_license_warning' => !$isLicensed
        ]);
    }
}
