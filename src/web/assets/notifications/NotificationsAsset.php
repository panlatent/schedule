<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2019 panlatent@gmail.com
 */

namespace panlatent\schedule\web\assets\notifications;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class NotificationsAsset
 *
 * @package panlatent\schedule\assets
 * @author Ryssbowh <boris@puzzlers.run>
 */
class NotificationsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@schedule/web/assets/notifications/dist';

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
        'notifications.js',
    ];
}