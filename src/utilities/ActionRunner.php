<?php

namespace panlatent\schedule\utilities;

use Craft;
use craft\base\Utility;
use panlatent\craft\actions\abstract\SavableActionInterface;
use panlatent\schedule\actions\HttpRequest;
use panlatent\schedule\Plugin;

class ActionRunner extends Utility
{
    public static function displayName(): string
    {
        return Craft::t('schedule', 'Action Runner');
    }

    public static function id(): string
    {
        return 'action-runner';
    }

    public static function icon(): ?string
    {
        return Craft::getAlias('@schedule/icons/action.svg');
    }

    public static function contentHtml(): string
    {
        return static::contentHtmlFromAction(new HttpRequest());
    }

    public static function contentHtmlFromAction(SavableActionInterface $action): string
    {
        $allActionTypes = Plugin::getInstance()->actions->getAllActionTypes();
        return Craft::$app->getView()->renderTemplate('schedule/_components/utilities/ActionRunner', [
            'action' => $action,
            'actionTypes' => $allActionTypes,
            'actionTypeOptions' => array_map(static fn($class) => ['label' => $class::displayName(), 'value' => $class], $allActionTypes),
        ]);
    }
}