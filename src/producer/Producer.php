<?php

namespace lisq\kafka\producer;

class Producer
{
    private $topic;
    private $config;

    public function __construct($topic, $config)
    {
        $this->topic = $topic;
        $this->config = $config;
    }

    public function publish($message)
    {
        $config = new \RdKafka\Conf();
        $config->set('metadata.broker.list', $this->$config['metadata.broker.list']);
        
        $producer = new \RdKafka\Producer($config);
        $topic = $producer->newTopic($this->topic);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($message));
        $producer->poll(0);
        $result = $producer->flush(10000);
        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            return false;
        }
        return true;
    }
}