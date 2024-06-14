<?php

namespace panlatent\schedule\di;

use Psr\Container\ContainerInterface;
use yii\di\Container;

readonly class ContainerAdapter implements ContainerInterface
{
    public function __construct(protected Container $container)
    {

    }

    public function get(string $id)
    {
        return $this->container->get($id);
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }
}