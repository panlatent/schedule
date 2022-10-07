<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\timers;

use Craft;
use panlatent\schedule\base\Timer;

/**
 * Class Minute
 *
 * @package panlatent\schedule\timers
 * @author Panlatent <panlatent@gmail.com>
 */
class Every extends Timer
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('schedule', 'Every');
    }

    // Properties
    // =========================================================================

    /**
     * @var string
     */
    private $_value = 'everyMinute';

    // Public Methods
    // =========================================================================

    /**
     * @return string
     */
    public function getValue(): string
    {
        switch ($this->getCronExpression()) {
            case '* * * * * *':
                $this->_value = 'everyMinute';
                break;
            case '0 * * * * *':
                $this->_value = 'hourly';
                break;
            case '0 0 * * * *':
                $this->_value = 'daily';
                break;
            case '0 0 1 * * *':
                $this->_value = 'monthly';
                break;
            case '0 0 1 1 * *':
                $this->_value = 'yearly';
                break;
            case '0 0 * * 0 *':
                $this->_value = 'weekly';
                break;
        }

        return $this->_value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value)
    {
        switch ($value) {
            case 'everyMinute':
                $this->setCronExpression('* * * * * *');
                break;
            case 'hourly':
                $this->setCronExpression('0 * * * * *');
                break;
            case 'daily':
                $this->setCronExpression('0 0 * * * *');
                break;
            case 'monthly':
                $this->setCronExpression('0 0 1 * * *');
                break;
            case 'yearly':
                $this->setCronExpression('0 0 1 1 * *');
                break;
            case 'weekly':
                $this->_value = '';
                $this->setCronExpression('0 0 * * 0 *');
                break;
        }

        $this->_value = $value;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return [
            'Every Minute' => 'everyMinute',
            'Hourly' => 'hourly',
            'Daily' => 'daily',
            'Monthly' => 'monthly',
            'Yearly' => 'yearly',
            'Weekly' => 'weekly',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/timers/Every', [
            'timer' => $this,
        ]);
    }
}