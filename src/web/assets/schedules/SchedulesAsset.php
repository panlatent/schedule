<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\web\assets\schedules;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\vue\VueAsset;
use craft\web\View;

/**
 * Class ScheduleAsset
 *
 * @package panlatent\schedule\assets
 * @author Panlatent <panlatent@gmail.com>
 */
class SchedulesAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@schedule/web/assets/schedules/dist';

    /**
     * @inheritdoc
     */
    public $depends = [
        CpAsset::class,
        VueAsset::class,
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'schedule.js',
    ];

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view): void
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