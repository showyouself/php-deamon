# php-deamon

php守护进程框架+

## 使用场景

需要一个程序一直在console循环执行，例如轮询消费队列消息等。

## 一、安装

```text
composer require zbin/php-deamon
```

## 相关配置

$processConfig轮询脚本配置

* action\_path ：执行的脚本格式"控制器/方法"
* run\_interval ：运行间隔\(秒\)
* param ： 传入参数

$swooleConfig主进程配置

* daemonize ：0:debug，1:后台运行
* foot\_ip ：ip
* foot\_port ： 监听端口，swoole默认9501
* message\_queue\_id ：轮询脚本反馈消息队列id，例如：0x0104a8a2

  ```text
  $new = new footman($processConfig, $swooleConfig);
  ```

## 二、在框架中使用

### yii2框架

新建一个footman对象，传入processConfig数组，如下脚本在每次执行完成后间隔的时间

* commands/SonController.php/actionTest 间隔10秒执行一次
* commands/SunController.php/actionRun 间隔5秒执行一次

  \`\`\`

  &lt;?php

  namespace app\commands;

  use yii\console\Controller;

  use zbin\footman;

class HelloController extends Controller { public function actionIndex\($message = 'hello world'\) { $processConfig = \[ \[ 'action\_path' =&gt; 'sub/run', 'run\_interval' =&gt; 5, 'param' =&gt; \['ben', 25,\] \], \[ 'action\_path' =&gt; 'son/test', 'run\_interval' =&gt; 10, 'param' =&gt; \['zeng', 2018,\] \] \]; // run in background 后台运行 // $swooleConfig = \['daemonize' =&gt; 1\]; // $new = new footman\($processConfig, $swooleConfig\); $new = new footman\($processConfig\); $new-&gt;run\(\); } }

```text
## 三、查看运行状态
直接使用http请求host:9501端口，即可得到运行状态，目前支持的入参为：
* show : 1、detail 
* action_path : 循环执行的脚本名 例如：sub-run
```

curl "127.0.0.1:9501?show=detail" 或者\(如果不希望浏览器可以访问，建议关闭出网端口\) view-source:[http://127.0.0.1:9501/?show=detail](http://127.0.0.1:9501/?show=detail)

```text
得到的结果如下：
```

&lt;&lt;=========\[son/test\]=========&gt;&gt; run\_count : 36 run\_interval : 10 last\_start\_time : 2018-03-24 06:37:20 last\_end\_time : 2018-03-24 06:37:20 last\_run\_msg : success last\_run\_ret : 0 run\_param : \["ben",25\]

\`\`\`

