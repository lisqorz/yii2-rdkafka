<?php

namespace lisqorz\rdkafka;

use lisqorz\rdkafka\producer\Producer;
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