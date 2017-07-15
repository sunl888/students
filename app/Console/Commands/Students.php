<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Students extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取所有学生的信息';

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
        app(GetStudentsInfo::class)->studentNumGenerate();
    }
}
