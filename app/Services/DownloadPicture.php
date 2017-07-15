<?php
/**
 * Created by PhpStorm.
 * User: Sunlong
 * Date: 2017/7/13
 * Time: 10:16
 */
namespace App\Services;

use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class DownloadPicture
{
    private $stuPhotoUri = 'http://211.70.176.123/dbsdb/tp.asp?xh=';//学生头像
    private $savePath = 'public/photos/';
    private $client = '';

    public function __construct()
    {
        if ($this->client == null){
            $this->client = new Client();
        }
    }

    /**
     * @param $stuNum
     * @param $path
     * @return bool
     */
    public function downloadPhoto($student)
    {
        if($student instanceof User){
            $student = $student->toArray();
        }
        $path = trim($student['department'] . DIRECTORY_SEPARATOR . $student['grade'] . DIRECTORY_SEPARATOR . $student['class']);
        //资源路径
        $savePath = $this->savePath . $path . DIRECTORY_SEPARATOR;
        if (!file_exists($savePath)) {
            mkdir($savePath, 0777, true);
        }
        $savePath .= $student['student_id'] . '.png';
        if (!file_exists($savePath)) {
            try{
                $this->client->get($this->stuPhotoUri . $student['student_id'], [
                    RequestOptions::SINK => $savePath, // 资源保存路径
                    RequestOptions::HTTP_ERRORS => false // 服务器返回500错误
                ]);
            }catch (\Exception $e){
                UserLog::error('Failed to connect to 211.70.176.123 port 80: Timed out: ' . $student['student_id']);
                //todo 因为123的网站不稳定，不得己加了个重试
                try{
                    $this->client->get($this->stuPhotoUri . $student['student_id'], [
                        RequestOptions::SINK => $savePath, // 资源保存路径
                        RequestOptions::HTTP_ERRORS => false // 服务器返回500错误
                    ]);
                }catch (\Exception $e){
                    UserLog::error('【try】 Failed to reConnect to 211.70.176.123 port 80: Timed out: ' . $student['student_id']);
                }
            }

        }
        return $savePath;
    }
}