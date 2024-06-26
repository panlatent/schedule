<?php

namespace panlatent\schedule\di;

use Psr\Container\NotFoundExceptionInterface;
use yii\di\NotInstantiableException;

class NotFoundException extends NotInstantiableException implements NotFoundExceptionInterface
{

}