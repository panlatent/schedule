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
    ];

    /**
     * @inheritdoc
     */
    public $css = [
        'css/chunk-vendors.css',
        'css/app.css',
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'js/chunk-vendors.js',
        'js/app.js',
    ];

    /**
     * @var array
     */
    public $languages = [
        'zh' => 'zh-CN',
        'zh-Hans' => 'zh-CN',
        'zh-Hant' =>'zh-TW',
        'de' => 'de',
        'pt' => 'pt',
        'es' => 'es',
        'da' => 'da',
        'fr' => 'fr',
        'nb-NO' => 'nb-NO',
        'it' => 'it',
        'ko' => 'ko',
        'ja' => 'ja',
        'nl' => 'nl',
        'vi' => 'vi',
        'ru-RU' => 'ru-RU',
        'tr-TR' => 'tr-TR',
        'pt-br' => 'pt-br',
        'fa' => 'fa',
        'th' => 'th',
        'id' => 'id',
        'bg' => 'bg',
        'pl' => 'pl',
        'fi' => 'fi',
        'sv-SE' => 'sv-SE',
        'el' => 'el',
        'sk' => 'sk',
        'ca' => 'ca',
        'cs-CZ' => 'cs-CZ',
        'ua' => 'ua',
        'tk' => 'tk',
        'ta' => 'ta',
        'lv' => 'lv',
        'af-ZA' => 'af-ZA',
        'ee' => 'ee',
        'sl' => 'sl',
        'ar' => 'ar',
        'he' => 'he',
        'lt' => 'lt',
        'mn' => 'mn',
        'kz' => 'kz',
        'hu' => 'hu',
        'ro' => 'ro',
        'ku' => 'ku',
        'ug-CN' => 'ug-CN',
        'km' => 'km',
        'sr' => 'sr',
        'eu' => 'eu',
        'kg' => 'kg',
        'hy' => 'hy',
        'hr' => 'hr',
    ];

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $language = Craft::$app->language;
        if (isset($this->languages[$language])) {
            $this->js[] = 'https://unpkg.com/element-ui@2.8.2/lib/umd/locale/' . $this->languages[$language] .'.js';
            $file = str_replace('-', '', $this->languages[$language]);
            Craft::$app->getView()->registerJs("ELEMENT.locale(ELEMENT.lang.{$file});", View::POS_END);
        }
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