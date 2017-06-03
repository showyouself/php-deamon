<?php
class Curl
{

	public $ch;
	/**
	 * @author zengbin 
	 * @since 本次：2016-1-8 上次： 2015-12-10
	 */
	function __construct()
	{
		// 初始化
		$this->ch = curl_init();
		// 释放返回结果
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
	}

	/**
	 * @author zengbin
	 * @version 2.0
	 * @since 本次：2016-1-8 上次： 2015-12-10
	 * @name  快速执行
	 * @param 必选  $url URL地址
	 * @param 可选  $method POST或者GET,不填为GET
	 * @param 可选  $data POST文件路径 
	 * @param 可选  $header 使用如下的形式的数组进行设置：array('Content-type: text/plain', 'Content-length: 100');
	 * @param 可选  $cookie 可选    多个cookie用分号分隔，分号后带一个空格(例如， "fruit=apple; colour=red")。  
	 * @return string 页面返回结果
	 */
	function rapid($url, $method="GET", $data = 'null', $header = 'null', $cookie = 'null')
	{

		$this->setUrl($url);
		$this->setMethod($method);
		$this->setTimeOut(5);
		if (strtolower($data) != 'null') {
			$this->setData($data);
		}
		if ($header != 'null') {
			$this->HttpHeader($header);
		}
		if (strtolower($cookie) != 'null') {
			$this->cookie($cookie);
		}
		// 执行并返回
		return $this->exec();
	}

	/**
	 *
	 * @param
	 *            你要访问的地址
	 */
	public function setUrl($url)
	{
		//禁用SSL协议，HTTPS访问
		$this->SSL_VERIFYHOST_VERIFYPEER();
		// 设置访问地址
		$url;
		curl_setopt($this->ch, CURLOPT_URL, $url);
	}

	/**
	 *
	 * @param
	 *            默认为GET，传参数POST为POST方式
	 */
	function setMethod($method)
	{
		// 设置访问方式
		if ($method == "POST") {
			//普通的post方式
			curl_setopt($this->ch, CURLOPT_POST, 1);
		}


	}

	/**
	 *
	 * @param
	 *            数组类型 如：$data=array('键'=>"值");
	 */
	function setData($data)
	{
		//设置post发送的数据
		curl_setopt($this->ch, CURLOPT_HEADER, 0);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);            
	}

	/**
	 *
	 * @name 设定HTTP请求中"Cookie: "部分的内容。多个cookie用分号分隔，分号后带一个空格(例如， "fruit=apple; colour=red")。
	 *      
	 */
	function cookie($cookie)
	{
		curl_setopt($this->ch, CURLOPT_COOKIE, $cookie);
	}

	/**
	 *
	 * @name 在HTTP请求中包含一个"User-Agent: "头的字符串。
	 */
	function userAgent($agent)
	{
		curl_setopt($this->ch, CURLOPT_USERAGENT, $agent);
	}

	/**
	 *
	 * @name 在HTTP请求头中"Referer: "的内容。
	 */
	function referer($referer)
	{
		curl_setopt($this->ch, CURLOPT_REFERER, $referer);
	}

	/**
	 *
	 * @name HTTP请求头中"Accept-Encoding: "的值。支持的编码有"identity"，"deflate"和"gzip"。如果为空字符串""，请求头会发送所有支持的编码类型。
	 */
	function acceptEncoding($encoding)
	{
		curl_setopt($this->ch, CURLOPT_ACCEPT_ENCODING, $encoding);
	}

	/**
	 * @name 禁止SSL效验操作为网络通信提供安全及数据完整性的一种安全协议。TLS与SSL在传输层对网络连接进行加密。
	 *它是由Netscape开发并内置于其浏览器中，
	 *用于对数据进行压缩和解压操作，并返回网络上传送回的结果。  
	 */
	function SSL_VERIFYHOST_VERIFYPEER(){
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
	}

	/**
	 *
	 * @name 一个用来设置HTTP头字段的数组。使用如下的形式的数组进行设置：
	 *       array('Content-type: text/plain', 'Content-length: 100')
	 */

	function HttpHeader($header)
	{
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
	}

	function setTimeOut($time)
	{
		curl_setopt($this->ch, CURLOPT_TIMEOUT,$time);
	}

	/**
	 * 执行并且放回结果
	 */
	function exec()
	{

		// 执行POST请求
		$output = curl_exec($this->ch);
		return $output;
	}
}
?>
