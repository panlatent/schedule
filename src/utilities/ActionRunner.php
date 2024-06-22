<?php

namespace panlatent\schedule\utilities;

use craft\base\Utility;
use panlatent\schedule\actions\HttpRequest;
use panlatent\schedule\Plugin;

class ActionRunner extends Utility
{
    public static function id(): string
    {
        return 'action-runner';
    }

    public static function contentHtml(): string
    {
        $allActionTypes = Plugin::getInstance()->actions->getAllActionTypes();
        return \Craft::$app->getView()->renderTemplate('schedule/_components/utilities/ActionRunner', [
            'action' => new HttpRequest(),
            'actionTypes' => $allActionTypes,
            'actionTypeOptions' => array_map(static fn($class) => ['label' => $class::displayName(), 'value' => $class], $allActionTypes),
        ]);
    }
}