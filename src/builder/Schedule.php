<?php

namespace panlatent\schedule\builder;

use panlatent\craft\actions\abstract\ActionInterface;
use panlatent\schedule\actions\Closure;
use panlatent\schedule\actions\Console;
use panlatent\schedule\actions\CraftConsole;
use panlatent\schedule\actions\HttpRequest;
use panlatent\schedule\models\Schedule as ScheduleModel;

final class Schedule
{
    use Handler, Interval;

    public function __construct(protected ActionInterface $action)
    {

    }

    public static function closure(\Closure $closure)
    {
        $action = new Closure();
        $action->closure = $closure;
        return new Schedule($action);
    }

    public static function exec(string $command, array $arguments = [])
    {
        $action = new Console();
        return new Schedule($action);
    }

    public static function console(string $command, array $arguments = [])
    {
        $action = new CraftConsole();
        return new Schedule($action);
    }

    public static function request(string $url)
    {
        $action = new HttpRequest();
        $action->url = $url;
        return new Schedule($action);
    }

    public function create(): ScheduleModel
    {
        $schedule = new ScheduleModel();
        $schedule->action = $this->action;
        return $schedule;
    }
}