<?php

namespace panlatent\schedule\di;

use Psr\Container\ContainerInterface;
use yii\di\Container;
use yii\di\NotInstantiableException;

readonly class ContainerAdapter implements ContainerInterface
{
    public function __construct(protected Container $container)
    {

    }

    public function get(string $id)
    {
        try {
            return $this->container->get($id);
        } catch (NotInstantiableException $exception) {
            throw new NotFoundException('', $exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }
}