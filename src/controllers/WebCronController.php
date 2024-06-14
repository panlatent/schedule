<?php

namespace panlatent\schedule\controllers;

use craft\web\Controller;
use panlatent\schedule\Plugin;
use panlatent\schedule\Scheduler;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class WebCronController extends Controller
{
    public function actionTrigger(): ?Response
    {
        $this->requireSiteRequest();

        $token = '';
        if ($this->request->isGet) {
            $token = $this->request->getQueryParam('token');
        } else {
            $token = $this->request->getBodyParam('token');
        }

        if ($token !== Plugin::getInstance()->settings->token) {
            throw new ForbiddenHttpException();
        }

        $scheduler = new Scheduler();
        $scheduler->maxConcurrent = 0;
        $scheduler->dispatch();

        return null;
    }
}