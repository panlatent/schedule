<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2019 panlatent@gmail.com
 */

namespace panlatent\schedule\web\assets\logs;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\vue\VueAsset;
use craft\web\View;

/**
 * Class LogAsset
 *
 * @package panlatent\schedule\web\assets\log
 * @author Panlatent <panlatent@gmail.com>
 */
class LogsAsset extends AssetBundle
{
    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $sourcePath = '@schedule/web/assets/logs/dist';

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
    public $css = [
        'https://unpkg.com/element-ui/lib/theme-chalk/index.css',
        'logs.css',
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'https://unpkg.com/element-ui@2.8.2/lib/index.js',
        'https://unpkg.com/vue-resource@1.5.1/dist/vue-resource.min.js',

    ];

    /**
     * @var array
     */
    public $languages = [
        'en' => 'en',
        'zh' => 'zh-CN',
        'zh-Hans' => 'zh-CN',
        'zh-Hant' =>'zh-TW',
        'de' => 'de'
    ];

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        $language = Craft::$app->language;
        if (isset($this->languages[$language])) {
            $this->js[] = 'https://unpkg.com/element-ui@2.8.2/lib/umd/locale/' . $this->languages[$language] .'.js';

            Craft::$app->getView()->registerJsVar('language', $language);
        }

        $this->js[] = 'logs.js';
    }

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);

        if ($view instanceof View) {
            $view->registerTranslations('schedule', [
                'Status',
                'Date',
                'Reason',
                'Start Date',
                'End Date',
                'Duration',
                'Output',
            ]);
        }
    }
}