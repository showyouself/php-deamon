<?php 
abstract class task{ 
	public function __construct($request)
	{
		$this->init($request);
	}
	public $request_type = NULL;
	public $request_data = NULL;

	public function init($request) {
		if (!empty($request)) { $this->request_data = $request; }	
		if (!empty($request['type'])) { $this->request_type = $request['type']; }	
	}

	abstract public function run(&$ret);

}
