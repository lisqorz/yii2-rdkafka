<?php

namespace lisq\kafka;

use RdKafka\Producer;
use yii\base\Component;
use yii\log\Dispatcher;

class Kafka extends Component
{
    public $rdkafka;
    public $hosts;
    public $consumers;

    /**
     * @var Dispatcher $logger
     */
    public $logger;

    public function init()
    {
        parent::init();

        if (!\Yii::$container->hasSingleton('kafka-connection')) {
            \Yii::$container->setSingleton('kafka-connection', function () {
                $config = new \RdKafka\Conf();
                $config->set('bootstrap.servers', $this->hosts);
                return new \RdKafka\Producer($config);
            });
        }
    }

    public function publishOne($topic, $msg)
    {
        $producer = \Yii::$container->get('kafka-connection');
        $topic = $producer->newTopic($topic);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($msg));
        $producer->poll(0);
        $result = $producer->flush(10000);
        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            return false;
        }
        return true;
    }

    public function publishBulk($data)
    {
        /** @var Producer $producer */
        $producer = \Yii::$container->get('kafka-connection');
        foreach ($data as $topic => $msgList) {
            $topic = $producer->newTopic($topic);
            foreach ($msgList as $msg) {
                $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($msg));
            }
        }
        $producer->poll(0);
        $result = $producer->flush(5000);
        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            return false;
        }
        return true;
    }

    public function getProducer()
    {
        return \Yii::$container->get('kafka-connection');
    }
}