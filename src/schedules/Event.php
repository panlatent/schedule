<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\schedules;

use Craft;
use panlatent\schedule\base\Schedule;
use panlatent\schedule\helpers\ClassHelper;
use ReflectionClass;
use yii\base\Component;
use yii\base\Event as BaseEvent;

/**
 * Class Event
 *
 * @package panlatent\schedule\schedules
 * @author Panlatent <panlatent@gmail.com>
 */
class Event extends Schedule
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('schedule', 'Event');
    }

    /**
     * @inheritdoc
     */
    public static function isRunnable(): bool
    {
        return true;
    }

    // Properties
    // =========================================================================

    /**
     * @var string|null
     */
    public ?string $className = null;

    /**
     * @var string|null
     */
    public ?string $eventName = null;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        $classSuggestions = [
            [
                'label' => '',
                'data' => $this->_getClassSuggestions(),
            ]
        ];

        return Craft::$app->getView()->renderTemplate('schedule/_components/schedules/Event/settings', [
            'schedule' => $this,
            'classSuggestions' => $classSuggestions,
        ]);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function execute(int $logId = null): bool
    {
        Craft::info("Event Schedule trigger event: $this->className::$this->eventName", __METHOD__);

        BaseEvent::trigger($this->className, $this->eventName);

        return true;
    }

    // Private Methods
    // =========================================================================

    /**
     * @return array
     */
    private function _getClassSuggestions(): array
    {
        $suggestions = [];
        foreach (ClassHelper::findClasses() as $class) {
            if (is_subclass_of($class, Component::class)) {
                $suggestions[] = [
                    'name' => $class,
                    'hint' => ClassHelper::getPhpDocSummary((new ReflectionClass($class))->getDocComment()),
                ];
            }
        }

        return $suggestions;
    }
}