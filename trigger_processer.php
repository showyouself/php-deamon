<?php
/**
 * Created by PhpStorm.
 * User: zengbin
 * Date: 2018/3/19
 * Time: 下午7:42
 */
namespace zbin;
class trigger_processer{
    public $action_path = NULL;     //任务名称
    public $run_interval = 10; //运行间隔
    public $param = NULL;        //config中配置的变量
    
    public $subPid = null;  //子进程id

    public $subRunRet = null;

    public function __construct(){}

    public function init($config){
        $this->action_path = !empty($config['action_path']) ? $config['action_path'] : NULL;
        $this->run_interval = !empty($config['run_interval']) ? $config['run_interval'] : 10 ;
        $this->param = !empty($config['param']) ? $config['param'] : NULL;
    }

    public function run($process) {
        
        $action_path = $this->action_path;
        $param = $this->param;
        $run_count = 0;
        while (true)
        {
            sleep($this->run_interval);

            //recycle
            \swoole_process::wait();

            //create new process
            $son_process = new \swoole_process(function ($son_process) use ($action_path, $param){
                $ret = \Yii::$app->runAction($action_path, $param);
                $exitParam = ['pid' => $son_process->pid, 'msg' => 'success', 'ret' => 0 , 'last_end_time' => time()];
                if ($ret === false) {
                    $exitParam['msg'] = "process :: processer {$action_path} run is return false";
                }
                $son_process->write(json_encode($exitParam));
                $son_process->exit();
            }, false);

            $last_run_start_time = time();
            $this->subPid = $son_process->start();
            echo "master[{$this->action_path}] :: start new woker pid-{$this->subPid}\n";

            //Synchronous blocking read
            $this->subRunRet = $son_process->read();
            $this->subRunRet = json_decode($this->subRunRet, true);

            $runStatus = array(
                'action_path' => $this->action_path,
                'run_interval' => $this->run_interval,
                'run_count' => ++$run_count,
                'last_start_time' => date('Y-m-d H:i:s', $last_run_start_time),
                'last_end_time' => date('Y-m-d H:i:s', $this->subRunRet['last_end_time']),
                'last_run_msg' => $this->subRunRet['msg'],
                'last_run_ret' => $this->subRunRet['ret'],
                'run_param' => $this->param,
            );

            //clear queue
            while (true) {
                $before_data = $process->pop();
                if (empty($before_data)) { sleep(1); continue; }
                $before_data = json_decode($before_data, true);
                $before_data[$runStatus['action_path']] = $runStatus;
                $process->push(json_encode($before_data));
                break;
            }
        }
    }
}
