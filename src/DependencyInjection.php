<?php

namespace lisqorz\rdkafka;

use Cassandra\Exception\ConfigurationException;
use lisqorz\rdkafka\controller\KafkaController;
use mikemadisonweb\rabbitmq\components\AbstractConnectionFactory;
use mikemadisonweb\rabbitmq\Configuration;
use yii\base\Application;
use yii\base\BootstrapInterface;

class DependencyInjection implements BootstrapInterface
{

    public function bootstrap($app)
    {
        echo 123;
        exit;
        $this->addControllers($app);
    }
    /**
     * Auto-configure console controller classes
     * @param Application $app
     */
    private function addControllers(Application $app)
    {
//        $config = $app->kafka->getConfig();
        $app->controllerMap['kafka'] = KafkaController::class;
    }
//
//    /**
//     * Register connections in service container
//     * @param Configuration $config
//     */
//    protected function registerConnections(Configuration $config)
//    {
//        foreach ($config->connections as $options) {
//            $serviceAlias = sprintf(Configuration::CONNECTION_SERVICE_NAME, $options['name']);
//            \Yii::$container->setSingleton($serviceAlias, function () use ($options) {
//                $factory = new AbstractConnectionFactory($options['type'], $options);
//                return $factory->createConnection();
//            });
//        }
//    }
}