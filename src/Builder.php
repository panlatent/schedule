<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule;

use Craft;
use omnilight\scheduling\Schedule;
use panlatent\schedule\events\ScheduleBuildEvent;
use yii\queue\JobInterface;

/**
 * Class Builder
 *
 * @package panlatent\schedule
 * @author Panlatent <panlatent@gmail.com>
 */
class Builder extends Schedule
{
    const EVENT_BEFORE_BUILD = 'beforeBuild';

    const EVENT_AFTER_BUILD = 'afterBuild';

    /**
     * @inheritdoc
     */
    public $cliScriptName = 'craft';

    /**
     * @param JobInterface $job
     * @return \omnilight\scheduling\Event
     */
    public function job(JobInterface $job)
    {
        return $this->call(function () use($job) {
            Craft::$app->getQueue()->push($job);
        });
    }

    /**
     *
     */
    public function build()
    {
        $event = new ScheduleBuildEvent([
            'builder' => $this,
            'events' => $this->_events,
        ]);

        if ($this->hasEventHandlers(static::EVENT_BEFORE_BUILD)) {
            $this->trigger(static::EVENT_BEFORE_BUILD, $event);
        }
        $this->_events = $event->events;

        // Add schedule events from scripts.
        if ($scripts = Plugin::$plugin->getSettings()->scripts) {
            call_user_func($scripts, $this);
        }

        $event = new ScheduleBuildEvent([
            'builder' => $this,
            'events' => $this->_events,
        ]);

        if ($this->hasEventHandlers(static::EVENT_AFTER_BUILD)) {
            $this->trigger(static::EVENT_AFTER_BUILD, $event);
        }

        $this->_events = $event->events;

        return $this;
    }
}