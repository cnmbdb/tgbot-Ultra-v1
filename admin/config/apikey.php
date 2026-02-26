<?php

/**
 * 波场 API key 配置（Laravel 后台）
 *
 * 约定：
 * - 统一从环境变量中读取，避免在代码仓库里写死真实 key
 * - TRONSCAN_API_KEYS / TRONGRID_API_KEYS 使用英文逗号分隔多个 key
 *
 * 后续可以在后台做一页表单，编辑并写入同名环境变量即可完成「一处配置，前后端&JOB 共用」。
 */

function tron_env_keys(string $envKey): array
{
    $raw = env($envKey, '');
    if ($raw === '' || $raw === null) {
        return [];
    }

    $items = array_map('trim', explode(',', $raw));

    // 过滤掉空字符串
    return array_values(array_filter($items, static function ($v) {
        return $v !== '';
    }));
}

return [
    // tronapikey: tronscan(https://tronscan.org/#/myaccount/apiKeys) 申请的 key，适用于 https://apilist.tronscanapi.com
    'tronapikey' => tron_env_keys('TRONSCAN_API_KEYS'),

    // gridapikey: trongrid(https://www.trongrid.io/) 申请的 key，适用于 https://api.trongrid.io
    'gridapikey' => tron_env_keys('TRONGRID_API_KEYS'),
];
