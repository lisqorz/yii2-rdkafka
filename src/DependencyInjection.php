<?php

namespace lisq\kafka;

use lisq\kafka\controller\KafkaController;
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