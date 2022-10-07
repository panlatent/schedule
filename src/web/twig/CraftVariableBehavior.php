<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\web\twig;

use panlatent\schedule\Plugin;
use yii\base\Behavior;

/**
 * Class CraftVariableBehavior
 *
 * @package panlatent\craft\dingtalk\web\twig
 * @author Panlatent <panlatent@gmail.com>
 */
class CraftVariableBehavior extends Behavior
{
    /**
     * @var Plugin
     */
    public $schedule;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->schedule = Plugin::getInstance();
    }
}