<?php

namespace panlatent\craft\actions\abstract;

class ArrayOutput implements OutputInterface
{
    public function __construct(private array $arr, private string $template)
    {

    }

    public function canStored(): bool
    {
        return true;
    }

    public function render(): string
    {
        return \Craft::$app->getView()->renderTemplate($this->template, $this->arr);
    }


}