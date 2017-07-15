<?php

namespace App\Listeners;

use App\Events\Event;
use App\Events\WriteStudents;
use App\Services\DownloadPicture;
use App\Services\LoginWapJWT;
use App\Services\UserLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class WriteStudentsListener implements ShouldQueue
{

    /**
     * Create the event listener.
     *
     * @return void
     */

    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Event $event
     * @return void
     */
    public function handle($event)
    {
        if ($event instanceof WriteStudents) {
            if(!empty($event->studentsInfoStack)){
                $i = 0;
                foreach ($event->studentsInfoStack as $item){
                    if(!isset($item['password'])){
                        //返回空则表示不能正常登陆

                        $info = app(LoginWapJWT::class)->login2Jwc($item['student_id'],$item['id_card']);
                        if(!empty($info)){
                            $event->studentsInfoStack[$i]['examinee_number'] = $info['examinee_number'];
                            $event->studentsInfoStack[$i]['mobile_phone'] = $info['mobile_phone'];
                            $event->studentsInfoStack[$i]['password'] = $info['password'];
                            $downloadedPath = app(DownloadPicture::class)->downloadPhoto($item);
                            if ($downloadedPath) {
                                $event->studentsInfoStack[$i]['photo'] = $downloadedPath;
                            }
                        }else{
                            //此学号不能正常登陆,从数组中移除
                            array_pull($event->studentsInfoStack,$i);
                        }
                    }
                    $i++;
                }
                //判断最后数组中还有没有记录
                if(!empty($event->studentsInfoStack)){
                    DB::table('users')->insert($event->studentsInfoStack);
                    UserLog::info('------------本次保存了 '.count($event->studentsInfoStack).' 条记录------------');
                    $event->studentsInfoStack = [];
                }
            }
        }
    }
}
