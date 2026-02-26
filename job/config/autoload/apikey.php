<?php

return [
    // tronapikey: tronscan(https://tronscan.org/#/myaccount/apiKeys) 申请的 key，适用于 https://apilist.tronscanapi.com
    // 实际数据从 t_sys_config 或环境变量中读取（见 app/function.php::getTronApiKeys）
    'tronapikey' => getTronApiKeys('tronscan'),

    // gridapikey: trongrid(https://www.trongrid.io/) 申请的 key，适用于 https://api.trongrid.io
    'gridapikey' => getTronApiKeys('trongrid'),
];
