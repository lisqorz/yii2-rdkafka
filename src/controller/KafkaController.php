<?php

namespace lisq\kafka\controller;

use lisq\kafka\consumer\ConsumerInterface;
use lisq\kafka\exception\Exception;
use lisq\kafka\Kafka;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use yii\console\Controller;

class KafkaController extends Controller
{
    public $name;

    public function actionConsume($name, $componentName = 'kafka')
    {
        $this->name = $name;
        /** @var Kafka $component */
        $component = \Yii::$app->{$componentName};
        /** @var ConsumerInterface $consume */
        $consume = $component->consumers[$name];
        $groupId = $consume['groupId'];
        $topic = $consume['topic'];
        $consumeTimeout = $consume['timeout'] ?? 120 * 1000;
        $class = $consume['consume'];

        if (empty($topic) || empty($groupId)) {
            throw new Exception("groupId 或 topic 不能为空");
        }
        $handle = \Yii::createObject($class);
        if (!($handle instanceof ConsumerInterface)) {
            throw new Exception('ConsumerInterface::execute must implements');
        }
        $conf = $this->getConf($component);
        $conf->set('group.id', $groupId);
        var_dump($conf);
        $consumer = new KafkaConsumer($conf);
        $consumer->subscribe($topic);
        while (true) {
            try {
                $message = $consumer->consume($consumeTimeout);
                if (!$message) {
                    continue;
                }
                if ($this->chkMessage($message)) {
                    continue;
                }
                $handle->execute($message);
                $consumer->commit();
            } catch (\Exception $e) {
                \Yii::error($e->getMessage(),'kafka'. $this->name);
            }
        }
    }

    private function getConf($component)
    {
        $rdConfig = $component->rdkafka;
        $conf = new Conf();
        foreach ($rdConfig as $key => $value) {
            $conf->set($key, $value);
        }
        $conf->set('client.id', $this->name);
        $conf->set('bootstrap.servers', $component->hosts);
        $conf->setRebalanceCb([$this, 'rebalanceCb']);
        return $conf;
    }

//    /**
//     * 异常通知
//     *
//     * @param $message
//     */
//    public function exceptionNotice($message)
//    {
//        if ($this->exceptionNoticeClass === null) {l
//            return;
//        }
//
//        $obj = Yii::createObject(['class' => $this->exceptionNoticeClass]);
//        if ($obj instanceof ExceptionNoticeInterface) {
//            $obj->send($message);
//        }
//    }

    /**
     * 当有新的消费进程加入或者退出消费组时，kafka 会自动重新分配分区给消费者进程，这里注册了一个回调函数，当分区被重新分配时触发
     *
     * @param KafkaConsumer $kafka
     * @param                        $err
     * @param array|null $partitions
     * @throws Exception
     */
    public function rebalanceCb(KafkaConsumer $kafka, $err, array $partitions = null)
    {
        var_dump($partitions,$err);
        switch ($err) {
            case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                // 消费进程分配分区时
                $str = count($partitions) > 0 ? '成功' : '失败';
                \Yii::info("Assign:消费进程分配分区 {$str}",'kafka'. $this->name);
                $kafka->assign($partitions);
                break;

            case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                // 消费进程退出分区时
                \Yii::info("Revoke:消费进程退出分区",'kafka'. $this->name);
                $kafka->assign(null);
                break;

            default:
                // 错误
                \Yii::error("Error:消费进程分配分区错误，信息：{$err}",'kafka'. $this->name);
                throw new Exception($err);
        }
    }


    /**
     * @param Message $message
     * @return bool|string
     * @throws Exception
     */
    protected function chkMessage(Message $message)
    {
        switch ($message->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                return false;
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                return "没有更多消息，请等待";
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                return "请求超时";
            default:
                throw new Exception($message->errstr(), $message->err);
        }
    }

    /**
     * 消费返回记录日志
     *
     * @param Message $message
     * @param bool $error 数据格式错误
     * @return bool
     */
    protected function messageLog(Message $message, $error = false)
    {
        $errorMsg = $message->err;
        if ($message->err !== 0) {
            $errorMsg = "[$message->err] " . $message->errstr();
        }

        $content = json_decode($message->payload, true);
        $logContent = "Topic：{$message->topic_name} , Partition：{$message->partition} , Offset：{$message->offset}, Error：{$errorMsg} , Data：{$content['message']}";

        if ($errorMsg == '-185') {
            return true;
        }

        if ($error || $errorMsg) {
            \Yii::error("消费返回记录错误：{$logContent}",'kafka'. $this->name);
        } else {
            \Yii::info("消费返回记录错误：{$logContent}",'kafka'. $this->name);
        }
    }

}