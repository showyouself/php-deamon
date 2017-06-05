<?php 
class db
{
	public $config = array(
			'servername' => 'localhost',
			'username' => '',
			'password' => '',
			'dbname' => '',
			);

	public $conn;
	public $result = NULL;

	public function __construct($db_config) { $this->init($db_config); }

	public function init($db_config) {
		$this->config = $db_config;
		return $this->tryConn(); 
	}

	private function tryConn()
	{
		// 创建连接
		$this->conn = new mysqli($this->config['servername'], $this->config['username'], $this->config['password'], $this->config['dbname']);
		// Check connection
		if ($this->conn->connect_error) { logger("ERROR", "connect to mysql failed: " . $conn->connect_error); return false; }
		return $this;
	}

	public function sql($sql) { 
		$this->clean_result();
		$this->result = @$this->conn->query($sql);
		if ($this->conn->error) {
			logger("ERROR","reconnec to mysql ," . $this->conn->error); 
			$this->tryConn(); 
			$this->clean_result();
			$this->result = $this->conn->query($sql);
		}
		if (!$this->result) { logger("ERROR", "query failed, sql:{$sql}，" . $this->conn->error); return false; }
		return $this; 
	}

	public function result_array(){
		$ret = array();
		if ( $this->result->num_rows > 0 ) {
			while($row = $this->result->fetch_assoc()) { $ret[] = $row; }
		}	
		return $ret;
	}

	public function result_row()
	{
		$ret = array();
		if ( $this->result->num_rows > 0 ) {
			while($row = $this->result->fetch_assoc()) { $ret = $row;  break; }
		}	
		return $ret;
	}

	private function clean_result() { $this->result = NULL; }
}
