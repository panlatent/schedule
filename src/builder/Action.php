<?php

namespace panlatent\schedule\builder;

use panlatent\schedule\actions\Closure;
use panlatent\schedule\actions\Console;
use panlatent\schedule\actions\CraftConsole;
use panlatent\schedule\actions\HttpRequest;

/**
 * @internal
 */
final class Action
{
    public function action(\Closure $closure)
    {
        $action = new Closure();
        return $this;
    }

    public function exec(string $command, array $arguments = [])
    {
        $action = new Console();
        return new Schedule($action);
    }

    public function console(string $command, array $arguments = [])
    {
        $action = new CraftConsole();
        return new Schedule($action);
    }

    public function request(string $url)
    {
        $action = new HttpRequest();
        $action->url = $url;
        return new Schedule($action);
    }
}