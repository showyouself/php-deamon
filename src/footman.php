<?php
/**
 * Created by PhpStorm.
 * User: zengbin
 * Date: 2018/3/18
 * Time: 下午2:58
 * how to install swoole :: http://zengbingo.com/p/268.html
 */
namespace zbin;
class footman{
    //swoole default port
    protected $swooleConfig = array(
        'daemonize' => 0,
        'foot_ip' => '127.0.0.1',
        'foot_port' => 9501,
        'message_queue_id' => 0x0104a8a2,
    ); //0、调试，1、后台执行

    //action process config
    protected $processConfig = array();

    //process queue msg woker
    protected $processQueue = null;

    //foot process
    protected $serv = null;

    /**
     * footman constructor.
     * @param $processConfig
     * example :: array(
    //        array(
    //            'action_path' => 'Hello/run',  // controller/action 
    //            'run_interval' => 20,           
    //            'param' => array(
    //                'name' => 'ben',
    //                'age' => 25
    //            )
    //        )
    //    )
     * @param null $swooleConfig array( 'daemonize' => 0 );  0 : debug , 1 :backgrounder
     * @throws \Exception
     */
    public function __construct($processConfig, $swooleConfig = null)
    {
        if (!class_exists('swoole_http_server')) {
            exit('swoole_http_server init failed, check swoole installed first!' . "\n");
        }
        if ($swooleConfig && is_array($swooleConfig)) {
            foreach ($swooleConfig as $key => $value) { $this->swooleConfig[$key] = $value; }
        }

        if (!is_array($this->processConfig)) { throw new \Exception('processConfig must be array'); }

        $this->processConfig = $processConfig;

    }



    public function run(){
        if (!$this->tryInitRootProcess()) {
            throw new \Exception('tryInitRootProcess failed :: check this $ip:$port is not available');
        }

        //register masters
        $this->runMasterProcess();

        //start foot
        $this->serv->start();
    }

    private function tryInitRootProcess(){

        //this $port is not available, because it is timeout or opening
        if (!self::checkPort($this->swooleConfig['foot_ip'], $this->swooleConfig['foot_port'])) { return true; }

        //create root process
        if ($this->creatRootProcess($this->swooleConfig['foot_ip'], $this->swooleConfig['foot_port'])) { return true; }

        return false;
    }

    private function creatRootProcess($ip, $port){
        $self = $this;
        $this->serv = new \swoole_http_server($ip, $port);
        $this->serv->set($this->swooleConfig);

        $this->serv->on('request', function ($req, $resp) use ($self) {
            $echo = '';
            $get = $req->get;
            !empty($get['show']) ? $show_type = $get['show'] : $show_type = NULL;
            !empty($get['action_path']) ? $action_path = str_replace('-', '/', $get['action_path']) : $action_path = NULL;
            while (true) {
                $list = $self->processQueue->pop();
                if (empty($list)) { sleep(1); continue; }

                $list = json_decode($list, true);
                foreach ($list as $item) {
                    if ($action_path != NULL && $action_path != $item['action_path']) { continue; }
                    $echo .= "\n";
                    $echo .=  '<<=========['. $item['action_path'] . "]=========>>\n";
                    $echo .=  'run_count : '. $item['run_count'] . "\n";

                    if ($show_type != 'detail') { continue; }
                    $echo .=  'run_interval : '. $item['run_interval'] . "\n";
                    $echo .=  'last_start_time : '. $item['last_start_time'] . "\n";
                    $echo .=  'last_end_time : '. $item['last_end_time'] . "\n";
                    $echo .=  'last_run_msg : '. $item['last_run_msg'] . "\n";
                    $echo .=  'last_run_ret : '. $item['last_run_ret'] . "\n";
                    $echo .=  'run_param : '. json_encode($item['run_param']) . "\n";
                }

                $self->processQueue->push(json_encode($list));
                break;
            }
            $resp->header("Content-Type", "text/html; charset=utf-8");
            $resp->end($echo);
        });
        
        return true;
    }

    private function runMasterProcess(){
        require_once('trigger_processer.php');
        foreach ($this->processConfig as $config) {
            $master_process = new trigger_processer();
            $process = new \swoole_process(function ($process) use ($master_process, $config){
                $master_process->init($config);
                $master_process->run($process);
            });
            $process->useQueue($this->swooleConfig['message_queue_id'], 2 | \swoole_process::IPC_NOWAIT);
            if ($this->serv->addProcess($process)){
                $this->processQueue = $process;
                echo 'manager :: run master process succ,' . json_encode($config) . "\n";
            }else {
                echo 'manager :: run master process failed,' . json_encode($config) . "\n";
            }
        }
        //init msg
        echo "masters :: clear old queue msg...\n";
        while (true){ if (empty($this->processQueue->pop())) { sleep(1); break; } }
        echo "masters :: init  new queue msg...\n";
        $this->processQueue->push(json_encode([]));
    }

    /**
     * @param $ip
     * @param $port
     * @return bool
     * check this $port availability
     */
    public static function checkPort($ip, $port){
        echo "testing ip : {$ip} , port : {$port}...\n";
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_nonblock($sock);
        socket_connect($sock, $ip, $port);
        socket_set_block($sock);
        $r = array($sock);
        $w = array($sock);
        $f = array($sock);
        if (socket_select($r, $w, $f, 5) == self::$PORT_STATUS_CLOSED) {
            echo "testing ip : {$ip} , port : {$port} is available\n";
            return true;
        }
        echo "testing ip : {$ip} , port : {$port} is not available\n";
        return false;
    }

    //port status
    protected static $PORT_STATUS_OPENING = 1;
    protected static $PORT_STATUS_CLOSED = 2;
    protected static $PORT_STATUS_TIMEOUT = 2;
}