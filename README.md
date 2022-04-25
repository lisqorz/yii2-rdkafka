yii2 kafka 生产者、消费者组件
====================
yii2 kafka 生产者、消费者组件

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist lisqorz/yii2-rdkafka "*"
```

or add

```
"lisqorz/yii2-rdkafka": "*"
```

to the require section of your `composer.json` file.


Usage
-----

open `common/config/main.php`

```php
'kafka' => [
    'class' => \lisq\kafka\Kafka::class,
    'hosts' => 'aaa:9092,bbb:9092,ccc:9092',
    'rdkafka' => [
        "auto.offset.reset" => "latest",
        "enable.auto.commit" => 'false',
        // auth config
        'api.version.request' => 'true',
        'sasl.mechanisms' => 'PLAIN',
        'sasl.username' => '',
        'sasl.password' => '',
        'security.protocol' => 'SASL_SSL',
        'ssl.ca.location' => dirname(dirname(__DIR__)).'/{YOUR_PATH}/ca-cert.pem',
    ],
    'consumers' => [
        'consumeName' => [
            'consume' => OffersConsume::class,
            'topic' => ['topic1'],
            'groupId' => 'php'
        ],
    ]
]
```
# Consumer Interface
``` 
use lisq\kafka\consumer\ConsumerInterface;
use RdKafka\Message;

class FirstConsume implements ConsumerInterface
{

    public function execute(Message $message)
    {
        try {
        } catch (\Exception $e) {
        }
    }
}
```

# Producer
```
// single publish
Yii::$app->kafka->publishOne(topic,message,pollTimeout,flushTimeout)

// batch publish
Yii::$app->kafka->publishBulk([
    'topic1'=>[
        ['message'=>'message1'],
        ['message'=>'message2'],
        ['message'=>'message3'],
    ],
    'topic2'=[
        ['message'=>'message1'],
        ['message'=>'message2'],
        ['message'=>'message3'],
    ]
],pollTimeout,flushTimeout)
```
# Start Consume
```php
./yii kafka/consume consumeName
```
