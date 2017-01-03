<?php
/**
 * Created by Lee.
 * User: Lee
 * Date: 2016-10-26
 * Time: 9:54
 */
namespace Mqueue;

abstract class MessageQueueModel
{
    protected $config;
    protected $conn;
    protected $channel;
    protected $exchange;
    protected $queue;
    public function __construct($config) {
        if(!class_exists('AMQPConnection')){
            exit('AMQP Extension Non-existent');
        }
        $this->config = $config;
        $this->connection();
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        if($this->conn) $this->conn->disconnect();
    }

    protected function connection(){
        $this->conn = new \AMQPConnection($this->config);
        if(!$this->conn->connect()){
            return false;
            die("cannot connect to the message queue server!");
        }
        $this->createChannel();
        return true;
    }

    /*
     * 创建Channel对象
     **********************/
    protected function createChannel() {
        //创建连接和channel
        $this->channel = new \AMQPChannel($this->conn);
    }

    /*
     * 创建Exchange对象
     * 用于发布消息
     **********************/
    public function createExchange($exchang_name) {
        //创建交换机
        $this->exchange = new \AMQPExchange($this->channel);
        $this->exchange->setName($exchang_name);
        $this->exchange->setType(AMQP_EX_TYPE_DIRECT); //direct类型
        $this->exchange->setFlags(AMQP_DURABLE);//持久化
        //echo "Exchange Status:".$this->exchange->declare()."\n";
        //echo "Exchange Status:".$this->exchange->declareExchange()."\n";

        return $this;
    }

    //发送消息
    public function publishMessage($message, $routingkey) {
        if(is_array($message) || is_object($message)) $message = json_encode($message);

        $this->exchange->publish($message, $routingkey);
    }

    /*
     * 创建队列对象
     * 用于订阅队列消息
     **********************/
    public function createQueue($queue_name) {
        //创建队列
        $this->queue = new \AMQPQueue($this->channel);
        $this->queue->setName($queue_name);
        $this->queue->setFlags(AMQP_DURABLE);//持久化
        //echo "Message Total:".$this->queue->declare()."\n";

        return $this;
    }

    /*
     * 绑定队列到路由
     * 用于订阅队列消息
     **********************/
    public function bindExchange($exchang_name, $route_key) {
        //绑定交换机与队列，并指定路由键
        $this->queue->bind($exchang_name, $route_key);

        return $this;
    }

    /*
     * 获取队列中消息的总数
     ********************************/
    public function getTotalNum() {
        return $this->queue->declare();//获取队列消息总数
    }

    /*
     * 以数组方式返回队列中的消息
     ********************************/
    public function returnMessage($maxNum = 100) {
        //非阻塞获取消息 返回消息列表
        $msg_num = $this->getTotalNum();//获取队列消息总数
        if($msg_num){
            $total = $msg_num;
            $recMsg = array();
            if($msg_num>$maxNum) $msg_num = $maxNum;

            for($i=0; $i<$msg_num; $i++){
                $message = $this->getMessage();//处理消息
                if($message){
                    array_push($recMsg, $message);
                }else{
                    break;
                }
            }
            return array('total'=>$total,'list'=>$recMsg,'listNum'=>$msg_num);
        }else{
            return false;
        }
    }

    //订阅消息回调函数
    public function getMessage(){
        $envelope = $this->queue->get(AMQP_AUTOACK) ;
        if($envelope){
            $message=$envelope->getBody();
            $message = $this->parseMessage($message);//解析消息
            return $message;
        }
        return null;
    }

    /*
     * 阻塞式接收队列消息
     **********************/
    public function receiveMessage() {
        //while(True){
            //阻塞方式回调逐条处理消息
            //必须实现 processMessage 方法
        $this->queue->consume(array($this, 'subscribeMessage'));
        //}
    }


    //订阅消息回调函数
    public function subscribeMessage($envelope){
        $msg=$envelope->getBody();
        $this->queue->ack($envelope->getDeliveryTag());
        $msg = $this->parseMessage($msg);//解析消息
        $this->processMessage($msg);//阻塞式处理消息 只适用于守护进程
    }

    /* 解析消息
     * 默认直接返回
     * 其它格式请重写该方法
     ******************************/
    protected function parseMessage($msg){
        return $msg;
    }

    /* 处理消息
     * 用于阻塞方式逐条处理消息
     ***************************/
    abstract protected function processMessage($msg);

}