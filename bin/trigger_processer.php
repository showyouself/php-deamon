<?php 
class trigger_processer{ 
	public $p_name = NULL;     //任务名称
	public $run_interval = 10; //运行间隔
	public $task = NULL;	   //任务
	public $file_base_path = './'; //类文件地址
	public $arg = NULL;        //config中配置的变量

	public function __construct($p_v)
	{
		$this->p_name = !empty($p_v['name']) ? $p_v['name'] : NULL;
		$this->run_interval = !empty($p_v['run_interval']) ? $p_v['run_interval'] : 10 ;
		$this->file_base_path = !empty($p_v['file_base_path']) ? $p_v['file_base_path'] : './';
		$this->arg = !empty($p_v['arg']) ? $p_v['arg'] : NULL;
	}

	private function init()
	{
		if (empty($this->p_name)) {
			logger("ERROR", "nothing for trigger_processer", array(__CLASS__, __FUNCTION__));
		}

		if (!file_exists($this->file_base_path . $this->p_name . '.php')) {
			logger("ERROR", "missing file ::" . $this->file_base_path . $this->p_name . '.php', array(__CLASS__, __FUNCTION__));
			return false;
		}else {
			require_once($this->p_name . '.php');
			if (!class_exists($this->p_name)) { 
				logger("ERROR", "class $this->p_name is undefined");
				return false; 
			}
			logger("DEBUG", "loadding $this->p_name.php success");
			$this->task =  new $this->p_name(NULL);
		}		
		return true;

	}

	public function run($process) {
		if (!$this->init()) { return false; }
		while (true) 
		{
			sleep($this->run_interval);
			if (!$this->task->run($this->arg)) {
				logger("ERROR", "calss $this->p_name run is return false");
				return false;
			}
			logger("ERROR", "calss $this->p_name do running");

		}
	}
}
