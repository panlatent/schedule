<?php

namespace panlatent\craft\actions\abstract;

interface OutputInterface
{
    public function canStored(): bool;

    public function getSettings(): array;
}