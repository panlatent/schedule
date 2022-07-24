<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\schedule\errors;

use yii\base\Exception;

/**
 * Class NotificationException
 *
 * @package panlatent\schedule\events
 * @author Ryssbowh <boris@puzzlers.run>
 */
class NotificationException extends Exception
{
    public static function noHandle(string $handle)
    {
        return new static("Notification with handle $handle doesn't exist");
    }

    public static function noId(int $id)
    {
        return new static("Notification with id $id doesn't exist");
    }
}