<?php

namespace panlatent\craft\actions\abstract;

class ConditionAction extends Action
{
    public function __construct(private readonly ActionInterface $action, $config = [])
    {
        parent::__construct($config);
    }

    public function getConditions(): array
    {
        return [];
    }

    public function getAction(): ActionInterface
    {
        return $this->action;
    }

    public function validateConditions(): bool
    {
        return true;
    }

    public function execute(ContextInterface $context): bool
    {



        return $this->action->execute($context);
    }
}