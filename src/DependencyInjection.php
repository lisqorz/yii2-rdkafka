<?php

namespace lisqorz\rdkafka;

use lisqorz\rdkafka\controller\KafkaController;
use yii\base\BootstrapInterface;

class DependencyInjection implements BootstrapInterface
{

    public function bootstrap($app)
    {
        $this->addControllers($app);
    }

    /**
     * 添加控制器到console
     * @param $app
     */
    private function addControllers($app)
    {
        if ($app instanceof \yii\console\Application) {
            $app->controllerMap["kafka"] = KafkaController::class;
        }
    }
}