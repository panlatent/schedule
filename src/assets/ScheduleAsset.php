<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\View;

/**
 * Class ScheduleAsset
 *
 * @package panlatent\schedule\assets
 * @author Panlatent <panlatent@gmail.com>
 */
class ScheduleAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@panlatent/schedule/assets/dist';

    /**
     * @inheritdoc
     */
    public $depends = [
        CpAsset::class,
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'Schedule.js',
    ];

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);

        if ($view instanceof View) {
            $view->registerTranslations('schedule', [
                'Schedule enabled.',
                'Schedule disabled.',
            ]);

            $view->registerTranslations('app', [
                'Could not create the group:',
                'Group renamed.',
                'Could not rename the group:',
                'What do you want to name the group?',
                'Are you sure you want to delete this group?',
            ]);
        }
    }
}