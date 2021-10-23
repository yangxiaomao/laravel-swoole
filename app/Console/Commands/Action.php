<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;

class Action extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'action:call {uri}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'php artisan action:call XXController@xxAction?a=2';

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
     * @return int
     */
    public function handle(Request $request)
    {
        $url = $this->argument('uri');

        // 获取控制器+方法
        $uri = parse_url($url, PHP_URL_PATH);
        $uri = explode('@', $uri);

        // 获取参数
        parse_str(parse_url($url, PHP_URL_QUERY), $param);

        $controller = $uri[0] ?? '';
        $action = $uri[1] ?? '';
        if (empty($controller) || empty($action)) {
            $this->info('The format (Controller@method) is required');
            return;
        }

        try {
            $container = app()->make("App\Http\Controllers\\" . $controller);
        } catch (\Exception $e) {
            $this->info($e->getMessage());
        }

        if ($param) {
            foreach ($param as $k => $v) {
                $request->offsetSet($k, $v);
            }
        }

        $result = app()->call([$container, $action], $param);
        $this->info($result);
    }
}
