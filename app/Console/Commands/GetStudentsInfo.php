<?php

/**
 * Created by PhpStorm.
 * User: Sunlong
 * Date: 2017/7/12
 * Time: 10:39
 */

namespace App\Console\Commands;

use App\Events\WriteStudents;
use App\Services\UserLog;
use Carbon\Carbon;
use GuzzleHttp\Client;

/**
 * Class GetStudentsInfo
 * @package App\Console\Commands
 */
class GetStudentsInfo
{
    //电脑版学工网上信息获取
    private $stuInfoBaseUri = 'http://211.70.176.38/SystemForm/StuFile/StuFile_Edit.aspx?StudentId=';
    private $cookies = [
        [
            "Domain" => "211.70.176.38",
            "HostOnly" => true,
            "HttpOnly" => false,
            "Name" => "CenterSoft",
            "Path" => "/",
            "SameSite" => "no_restriction",
            "Secure" => false,
            "Session" => true,
            "StoreId" => "0",
            "Value" => "6DB3A98D59A57EF5C4C73CCF64F47662130C168BB1881AA35CBB9C17B0035883414A90005AF0F9C909E54AA52097DA153BE5C4B0489D582CDAC74BDEB038CB151A0D76CF4B416EEB0B4E58FE8BC82B0A68ECA5105DC541EB222F8C87EE3D99B5993E36B9BFBDA43487B4D2EB1DCABDBAC43241E3E51385CC64E78F99F8549BCD3578FABF9537854EAC23E8E4A45AB9BC0AAE3A794457B9FB5A91BFA595DB18C7E5513A557B44922411CAE4518ACC004231675D0BB9A6A7DEC0B08363B38E418E",
        ]
    ];
    private $client = '';
    private $pattren = '/StuFileInfo1_Name">(?<student_name>.*)<\/span>[\s\S]+?StuFileInfo1_Sex">(?<student_sex>.*)B<\/span>[\s\S]+?StuFileInfo1_IdCard">(?<id_card>.*)<\/span>[\s\S]+?StuFileInfo1_Birthday">(?<date_of_birth>.*)<\/span>[\s\S]+?StuFileInfo1_Nation">(?<nation>.*)<\/span>[\s\S]+?StuFileInfo1_Polity">(?<political_outlook>.*)<\/span>[\s\S]+?StuFileInfo1_CollegeName">(?<department>.*)<\/span>[\s\S]+?StuFileInfo1_SpecialtyName">(?<major>.*)<\/span>[\s\S]+?StuFileInfo1_SpeGrade">(?<grade>.*)<\/span>[\s\S]+?StuFileInfo1_ClassName">(?<class>.*)<\/span>[\s\S]+?StuFileInfo1_StudentAddress">(?<place_of_origin>.*)<\/span>[\s\S]+?StuFileInfo1_FamillyAddress">(?<home_address>.*)<\/span>[\s\S]+?StuFileInfo1_FamillyPost">(?<postalcode>.*)<\/span>[\s\S]+?StuFileInfo1_FamillyTel">(?<home_tel>.*)<\/span>[\s\S]+?/i';
    private $studentsInfoStack = [];
    private $majors = [1, 2, 3, 4, 5, 6, 7, 11, 21, 22];//这里的11是4个12级的学生

    public function __construct()
    {
        if ($this->client == null) {
            $this->client = new Client([
                'timeout' => 0,
            ]);
        }
    }

    /**
     * 学号生成
     */
    public function studentNumGenerate()
    {
        /*$this->getStudentInfoFromXGW('1207010221');
        $this->getStudentInfoFromXGW('1207030203');
        $this->getStudentInfoFromXGW('1208030109');
        $this->getStudentInfoFromXGW('1209040124');
        $this->getStudentInfoFromXGW('1210020113');
        $this->getStudentInfoFromXGW('1212010102');
        $this->getStudentInfoFromXGW('1212020217');
        $this->getStudentInfoFromXGW('1212020350');
        $this->getStudentInfoFromXGW('1212030246');
        if (!empty($this->studentsInfoStack)) {
            event(new WriteStudents($this->studentsInfoStack));
            $this->studentsInfoStack = [];
        }
        dd('test end');*/
        $i = 0;
        // 年级 11-16
        for ($grade = 17; $grade <= 17; $grade++) {
            // 学院 1-14
            for ($department = 1; $department <= 14; $department++) {
                // 专业 01-07->普高 21-22->对口
                for ($majorIndex = 1; $majorIndex <= count($this->majors); $majorIndex++) {
                    // 班级 01-08
                    for ($class = 1; $class <= 8; $class++) {
                        // 学号 01-66
                        for ($stu = 1; $stu <= 66; $stu++) {
                            $studentNum = trim($grade . sprintf("%02d", $department) . sprintf("%02d", $this->majors[$majorIndex - 1]) . sprintf("%02d", $class) . sprintf("%02d", $stu));
                            $this->getStudentInfoFromXGW($studentNum);
                            echo ++$i . ' ' . $studentNum . "\n";
                        }
                        // 将该班所有同学的信息保存
                        if (!empty($this->studentsInfoStack)) {
                            event(new WriteStudents($this->studentsInfoStack));
                            $this->studentsInfoStack = [];
                        }
                    }
                }
            }
        }
    }

    /**
     * 从学工网上获取学生信息
     * @param $stuNum
     * @return bool
     */
    public function getStudentInfoFromXGW($stuNum)
    {
        $stuInfoUri = trim($this->stuInfoBaseUri . $stuNum);
        $res = null;
        $capture = [];

        try {
            $jar = new \GuzzleHttp\Cookie\CookieJar(false, $this->cookies);
            $res = $this->client->request('GET', $stuInfoUri, [
                'cookies' => $jar
            ]);
        } catch (\Exception $e) {
            UserLog::error('Connection was reset: ' . $stuNum);
            return false;
        }
        $content = mb_convert_encoding($res->getBody(), 'UTF-8', 'gbk');
        if (1 === preg_match($this->pattren, $content, $capture)) {
            if (empty($capture['student_name']) || empty($capture['department']) || empty($capture['major'])) {
                UserLog::warning('该学生信息不完整: ' . $stuNum . ' ,可能是该学生不在本校.');
                return false;
            }
            $student = [
                'student_id' => $stuNum,
                'student_name' => $capture['student_name'],//姓名
                'student_sex' => $capture['student_sex'],//性别
                'id_card' => $capture['id_card'],//身份证号码
                'date_of_birth' => $capture['date_of_birth'],//出生日期
                'nation' => $capture['nation'],//民族
                'political_outlook' => $capture['political_outlook'],//政治面貌
                'department' => $capture['department'],//学院
                'major' => $capture['major'],//专业
                'grade' => $capture['grade'],//年级
                'class' => $capture['class'],//班级
                'place_of_origin' => $capture['place_of_origin'],//籍贯
                'home_address' => $capture['home_address'],//家庭地址
                'postalcode' => $capture['postalcode'],//邮政编码
                'home_tel' => $capture['home_tel'],//家庭电话
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            if (!empty($student)) {
                //UserLog::info('学号: ' . $stuNum . ' 抓取成功.');
                $this->studentsInfoStack[] = $student;
            }
        } else {
            return false;
        }
        return true;
    }
}