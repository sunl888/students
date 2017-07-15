<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('student_id',15)->comment("学号");
            $table->string('student_name',40)->comment("姓名")->nullable();
            $table->string('student_sex',6)->comment("性别")->nullable();
            $table->string('department',30)->comment("院系")->nullable();
            $table->string('major',30)->comment("专业")->nullable();
            $table->string('grade',30)->comment("年级")->nullable();
            $table->string('class',40)->comment("班级")->nullable();
            $table->string('nation',20)->comment("民族")->nullable();
            $table->string('place_of_origin',40)->comment("籍贯")->nullable();
            $table->string('date_of_birth',30)->comment("出生日期")->nullable();
            $table->string('political_outlook',30)->comment("政治面貌")->nullable();
            $table->string('id_card',20)->comment("身份证号码");
            $table->string('examinee_number',40)->comment("高考考生号")->nullable();
            $table->string('mobile_phone',15)->comment("手机号码")->nullable();
            $table->string('home_tel',20)->comment("家庭联系方式")->nullable();
            $table->string('password',30)->comment("教务处登陆密码")->nullable();
            $table->string('photo')->comment("头像地址")->nullable();
            $table->string('home_address')->comment("家庭地址")->nullable();
            $table->string('postalcode',10)->comment("邮政编码")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
