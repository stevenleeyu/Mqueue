<?php
/**
 * Created by PhpStorm.
 * User: Transn
 * Date: 2016-11-22
 * Time: 12:55
 */
namespace Home;

use Mqueue\TestQueueModel;

include_once 'Mqueue/functions.php';


$e_name='testExchange';//交换机名
$q_name='testQueue';//队列名
$k_route='testKey';//路由key

$qModel = new TestQueueModel();


$qModel->createQueue($q_name);
//阻塞式处理
$qModel->receiveMessage();
//$qModel->createQueue($q_name)->bindExchange($e_name, $k_route)->receiveMessage();

//返回消息队列中获取的消息
//$messages = $qModel->createQueue($q_name)->bindExchange($e_name, $k_route)->returnMessage(2);
$total = $qModel->getTotalNum();
echo $total;
$messages = $qModel->returnMessage(2);

$messages = $qModel->getMessage();
//$messages = $qModel->createQueue($q_name)->bindExchange($e_name, $k_route)->getMessage();
var_dump($messages);