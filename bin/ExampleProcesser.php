<?php 
/*
   自定义[processer]进程类示例
   1、需要加载的类必须现在config.php/processer_config中定义 
   2、自定义类必须继承类task，并且重写run方法,将返回结果赋值给$ret。
   3、run方法执行成功只需返回true，表示执行成功，return false表示错误，run不再继续执行
*/
class ExampleProcesser extends task {
	public function __construct($request)
	{
		parent::__construct($request);
	}

	public function run(&$ret)
	{
		logger("DEBUG", "进程 example_processer 执行{$ret['count']}次");
		$ret['count'] += 1;
		return false;
//		return true;
	}
}
