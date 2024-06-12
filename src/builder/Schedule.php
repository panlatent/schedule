<?php

namespace panlatent\schedule\builder;

use panlatent\craft\actions\abstract\ActionInterface;
use panlatent\schedule\actions\Command;
use panlatent\schedule\actions\Console;
use panlatent\schedule\actions\HttpRequest;

final class Schedule
{
    use Handler, Interval;

    public function __construct(protected ActionInterface $action)
    {

    }

    public static function command(string $command, array $arguments = [])
    {
        $action = new Command();
        return new Schedule($action);
    }

    public static function request(string $url)
    {
        $action = new HttpRequest();
        $action->url = $url;
        return new Schedule($action);
    }
}