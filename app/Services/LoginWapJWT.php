<?php
/**
 * Created by PhpStorm.
 * User: Sunlong
 * Date: 2017/7/13
 * Time: 22:20
 */

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;

class LoginWapJWT
{
    private $loginUri = 'http://211.70.176.123/wap/index.asp';
    private $studentInfoUri = 'http://211.70.176.123/wap/grxx.asp';
    public $client = null;
    private $cookies = null;
    private $info = [];

    public function __construct(Client $client = null)
    {
        if ($this->client == null)
            $this->client = new Client([
                RequestOptions::TIMEOUT => 0
            ]);
    }

    public function login2Jwc($studentNum = null, $idCard = null)
    {
        $res = null;
        $request = new Request('GET', $this->loginUri);
        try {
            $res = $this->client->send($request, [
                RequestOptions::QUERY => [
                    'xh' => $studentNum,
                    'sfzh' => $idCard
                ]
            ]);
            $this->cookies = new \GuzzleHttp\Cookie\CookieJar();
            $this->cookies->extractCookies($request, $res);
        } catch (RequestException $e) {
            try {
                $res = $this->client->send($request, [
                    RequestOptions::QUERY => [
                        'xh' => $studentNum,
                        'sfzh' => $idCard
                    ]
                ]);
                $this->cookies = new \GuzzleHttp\Cookie\CookieJar();
                $this->cookies->extractCookies($request, $res);
            } catch (RequestException $e) {
                UserLog::error('该学号登陆失败：' . $studentNum);
                return false;
            }
        }
        $content = mb_convert_encoding($res->getBody(), 'UTF-8', 'gbk');
        $m = [];
        if (1 === preg_match('/<SCRIPT language=JavaScript> window\.alert\(\'欢迎登陆教务系统！\'\);location\.href=\'main\.asp\'<\/SCRIPT>/', $content, $m)) {
            //信息捕获成功
            $this->getStudentInfoFromJWC();
        }
        if (!empty($this->info))
            print_r("\t" . $studentNum . '  ' . $idCard . '  ' . $this->info['password'] . "\n");
        return $this->info;
    }

    public function getStudentInfoFromJWC()
    {
        $res = null;
        $res = $this->client->get($this->studentInfoUri, [
            RequestOptions::COOKIES => $this->cookies
        ]);
        $content = mb_convert_encoding($res->getBody(), 'UTF-8', 'gbk');
        $m = [];
        if (1 === preg_match('/高考考生号[\s\S]+?<td align="center" width="150" height="22" valign="middle">(?<examinee_number>.*)<\/td>[\s\S]+?教务系统登陆密码[\s\S]+?<td align="center" width="170" height="22" valign="middle">(?<mobile_phone>.*)<\/td>[\s\S]+?<td align="center" width="150" height="22" valign="middle">(?<password>.*)<\/td>/i', $content, $m)) {
            if (isset($m['password']) && !empty($m['password'])) {
                $this->info = [
                    'examinee_number' => $m['examinee_number'],
                    'mobile_phone' => $m['mobile_phone'],
                    'password' => $m['password'],
                ];
            }
        }
    }
}