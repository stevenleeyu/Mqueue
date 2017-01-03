<?php
namespace Home;
use Mqueue\TestQueueModel;
include_once 'Mqueue/functions.php';


$e_name='testExchange';//交换机名
$q_name='testQueue';//队列名
$k_route='testKey';//路由key

$qModel = new TestQueueModel();

$message = array(1,2,3);
$qModel->createExchange($e_name)->publishMessage($message, $k_route);

//阻塞式处理
//$qModel->createQueue($q_name)->bindExchange($e_name, $k_route)->receiveMessage(true);
?>