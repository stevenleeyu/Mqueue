<?php
/**
 * Created by PhpStorm.
 * User: Transn
 * Date: 2016-10-26
 * Time: 18:10
 */
namespace Mqueue;

class TestQueueModel extends MessageQueueModel
{
    public function __construct($config = null)
    {
        if(empty($config)) $config = array(
            'host'=>'10.5.123.110',
            'port'=>'5672',
            'login'=>'woordee_develop',
            'password'=>'woordee-rabbitmq-123456',
            'vhost'=>'/'
        );
        parent::__construct($config);
    }

    //解析消息
    protected function parseMessage($msg){
        return json_decode($msg, true);
    }

    protected function processMessage($msg){
        var_dump($msg)."\n";
    }
}

