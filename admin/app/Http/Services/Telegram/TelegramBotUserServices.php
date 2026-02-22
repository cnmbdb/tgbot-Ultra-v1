<?php

namespace App\Http\Services\Telegram;

use App\Models\Telegram\TelegramBotUser;
use App\Models\Telegram\TelegramBotGroup;

class TelegramBotUserServices
{
    /**
     * 用户关注
     * @param $data json数据
     */
    public function userfollow($request)
    {
        //私聊
        if($request['chattype'] == 'private'){
            $model = TelegramBotUser::where('bot_rid',$request['bot_rid'])->where('tg_uid',$request['tguserid'])->first();
            //不存在,且新关注
            if(empty($model) && $request['status'] == 'member'){
                TelegramBotUser::create([
                    'bot_rid' => $request['bot_rid'],
                    'tg_uid' => $request['tguserid'],
                    'tg_username' => $request['tgusername'],
                    'status' => 1, //当前状态:1使用中,2已停用
                    'tg_nickname' => $request['tgusernickname'],
                    'first_time' => now()
                ]);
            //已存在,重复关注
            }elseif ($request['status'] == 'member') {
                $save_data = [];
                $save_data['status'] = 1;
                $save_data['tg_username'] = $request['tgusername'];
                $save_data['tg_nickname'] = $request['tgusernickname'];
                $save_data['last_time'] = now();
                $save_data['stop_time'] = null;
                TelegramBotUser::where('rid',$model['rid'])->update($save_data);
            }
            //已存在,取消关注
            if(!empty($model) && $request['status'] == 'kicked'){
                $save_data = [];
                $save_data['status'] = 2;
                $save_data['stop_time'] = now();
                TelegramBotUser::where('rid',$model['rid'])->update($save_data);
            }
        }
        
        //群组
        if($request['chattype'] == 'supergroup' || $request['chattype'] == 'group'){
            $model = TelegramBotGroup::where('bot_rid',$request['bot_rid'])->where('tg_groupid',$request['chatid'])->first();
            //不存在,且新关注
            if(empty($model) && ($request['status'] == 'member' || $request['status'] == 'administrator')){
                TelegramBotGroup::create([
                    'bot_rid' => $request['bot_rid'],
                    'group_type' => $request['chattype'],
                    'tg_groupid' => $request['chatid'],
                    'tg_groupusername' => $request['tgusername'],
                    'tg_groupnickname' => $request['grouptitle'],
                    'status' => 1, //当前状态:1使用中,2已停用
                    'first_time' => now(),
                    'is_admin' => $request['status'] == 'administrator' ?'Y':'N'
                ]);
            //已存在,重复关注
            }elseif ($request['status'] == 'member' || $request['status'] == 'administrator') {
                $save_data = [];
                $save_data['status'] = 1;
                $save_data['tg_groupusername'] = $request['tgusername'];
                $save_data['tg_groupnickname'] = $request['grouptitle'];
                $save_data['last_time'] = now();
                $save_data['stop_time'] = null;
                $save_data['is_admin'] = $request['status'] == 'administrator' ?'Y':'N';
                TelegramBotGroup::where('rid',$model['rid'])->update($save_data);
            }
            
            //已存在,取消关注
            if(!empty($model) && ($request['status'] == 'kicked' || $request['status'] == 'left')){
                $save_data = [];
                $save_data['status'] = 2;
                $save_data['stop_time'] = now();
                TelegramBotGroup::where('rid',$model['rid'])->update($save_data);
            }
        }
    }
    
    /**
     * 改名称
     * @param $data json数据
     */
    public function changenickname($request)
    {
        //私聊
        if($request['chattype'] == 'private'){
            $model = TelegramBotUser::where('bot_rid',$request['bot_rid'])->where('tg_uid',$request['tguserid'])->first();
            //不存在,且新关注
            if(empty($model) && $request['status'] == 'member'){
                TelegramBotUser::create([
                    'bot_rid' => $request['bot_rid'],
                    'tg_uid' => $request['tguserid'],
                    'tg_username' => $request['tgusername'],
                    'status' => 1, //当前状态:1使用中,2已停用
                    'tg_nickname' => $request['tgusernickname'],
                    'first_time' => now()
                ]);
            //已存在,重复关注
            }elseif ($request['status'] == 'member') {
                $save_data = [];
                $save_data['status'] = 1;
                $save_data['tg_username'] = $request['tgusername'];
                $save_data['tg_nickname'] = $request['tgusernickname'];
                $save_data['last_time'] = now();
                $save_data['stop_time'] = null;
                TelegramBotUser::where('rid',$model['rid'])->update($save_data);
            }
            //已存在,取消关注
            if(!empty($model) && $request['status'] == 'kicked'){
                $save_data = [];
                $save_data['status'] = 2;
                $save_data['stop_time'] = now();
                TelegramBotUser::where('rid',$model['rid'])->update($save_data);
            }
        }
        
        //群组
        if($request['chattype'] == 'supergroup' || $request['chattype'] == 'group'){
            $model = TelegramBotGroup::where('bot_rid',$request['bot_rid'])->where('tg_groupid',$request['chatid'])->first();
            //不存在,且新关注
            if(empty($model)){
                TelegramBotGroup::create([
                    'bot_rid' => $request['bot_rid'],
                    'group_type' => $request['chattype'],
                    'tg_groupid' => $request['chatid'],
                    'tg_groupusername' => $request['chatusername'],
                    'tg_groupnickname' => $request['newchattitle'],
                    'status' => 1, //当前状态:1使用中,2已停用
                    'first_time' => now()
                ]);
            //已存在
            }else{
                $save_data = [];
                $save_data['status'] = 1;
                $save_data['tg_groupusername'] = $request['chatusername'];
                $save_data['tg_groupnickname'] = $request['newchattitle'];
                $save_data['last_time'] = now();
                $save_data['stop_time'] = null;
                TelegramBotGroup::where('rid',$model['rid'])->update($save_data);
            }
        }
    }
    
}