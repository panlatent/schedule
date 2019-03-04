<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\base;

use craft\base\SavableComponent;

abstract class Timer extends SavableComponent implements TimerInterface
{
    use TimerTrait;

    /**
     * @inheritdoc
     */
    public function getMinute(): string
    {
        return (($this->minute !== null) && ($this->minute !== '')) ? $this->minute : '*';
    }

    /**
     * @inheritdoc
     */
    public function getHour(): string
    {
        return (($this->hour ?? '*' !== null) && ($this->hour ?? '*' !== '')) ? $this->hour ?? '*' : '*';
    }

    /**
     * @inheritdoc
     */
    public function getDay(): string
    {
        return (($this->day ?? '*' !== null) && ($this->day ?? '*' !== '')) ? $this->day ?? '*' : '*';
    }

    /**
     * @inheritdoc
     */
    public function getMonth(): string
    {
        return (($this->month ?? '*' !== null) && ($this->month ?? '*' !== '')) ? $this->month ?? '*' : '*';
    }

    /**
     * @inheritdoc
     */
    public function getWeek(): string
    {
        return (($this->week ?? '*' !== null) && ($this->week ?? '*' !== '')) ? $this->week ?? '*' : '*';
    }

    /**
     * Returns cron expression.
     *
     * @return string
     */
    public function getCronExpression(): string
    {
        return sprintf('%s %s %s %s %s *', $this->getMinute(), $this->getHour(), $this->getDay(), $this->getMonth(), $this->getWeek());
    }

    /**
     * @param string $cron
     */
    public function setCronExpression(string $cron)
    {
        list($this->minute, $this->hour, $this->day, $this->month, $this->week, ) = explode(' ', $cron);
    }
}