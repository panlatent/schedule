<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\services;

use craft\errors\MissingComponentException;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\Component as ComponentHelper;
use panlatent\schedule\base\TimerInterface;
use panlatent\schedule\timers\Custom;
use panlatent\schedule\timers\Every;
use panlatent\schedule\timers\MissingTimer;
use yii\base\Component;

class Timers extends Component
{
    /**
     * @event RegisterComponentEvent
     */
    const EVENT_REGISTER_TIMER_TYPES = 'registerTimerTypes';

    /**
     * @return string[]
     */
    public function getAllTimerTypes(): array
    {
        $types = [
            Custom::class,
            Every::class,
        ];

        $event = new RegisterComponentTypesEvent([
            'types' => $types,
        ]);

        $this->trigger(self::EVENT_REGISTER_TIMER_TYPES, $event);

        return $event->types;
    }

    /**
     * @param mixed $config
     * @return TimerInterface
     */
    public function createTimer($config): TimerInterface
    {
        try {
            $timer = ComponentHelper::createComponent($config, TimerInterface::class);
        } catch (MissingComponentException $exception) {
            unset($config['type']);
            $timer = new MissingTimer($config);
        }

        return $timer;
    }
}