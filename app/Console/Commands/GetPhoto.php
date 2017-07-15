<?php
/**
 * Created by PhpStorm.
 * User: Sunlong
 * Date: 2017/7/14
 * Time: 12:03
 */

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use App\Services\DownloadPicture;

class GetPhoto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:photo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '判断所有学生的头像是否存在，如果不存在则重新下载并保存到数据库中。';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $students = User::all();
        foreach ($students as $item) {
            if (!file_exists($item->photo)) {
                //头像文件不存在
                $downloadPath = app(DownloadPicture::class)->downloadPhoto($item);
                $item->photo = $downloadPath;
                echo 'Download ' . $item->student_id . "avatar successd.\n";
                $tmp = $item->update();
                if ($tmp) {
                    echo 'Save ' . $item->student_id . "avatar successd.\n";
                }
            }
        }
    }
}
