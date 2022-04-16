<?php

namespace lisq\kafka;

use lisq\kafka\producer\Producer;
use yii\base\Component;

class Kafka extends Component
{
    public $rdkafka;
    public $consumers;

    public function getProducer($topic)
    {
        return new Producer($topic,$this->rdkafka);
    }
}