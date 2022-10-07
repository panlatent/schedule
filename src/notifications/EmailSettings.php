<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2019 panlatent@gmail.com
 */

namespace panlatent\schedule\notifications;

use craft\base\Model;

/**
 * Class EmailSettings
 *
 * @package panlatent\schedule\services
 * @author Ryssbowh <boris@puzzlers.run>
 */
class EmailSettings extends Model
{
    /**
     * @var boolean
     */
    public $onError = false;

    /**
     * @var boolean
     */
    public $onSuccess = false;

    /**
     * @var array
     */
    protected $_emails = [];

    /**
     * @inheritDoc
     */
    public function defineRules(): array
    {
        return [
            [['onError', 'onSuccess'], 'boolean'],
            ['emails', 'validateEmails']
        ];
    }

    /**
     * Validate emails
     */
    public function validateEmails()
    {
        foreach ($this->emails as $email) {
            $email = \Craft::parseEnv($email['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addError('emails', \Craft::t('schedule', '{email} is not a valid email address', ['email' => $email]));
            }
        }
    }

    /**
     * Emails getter
     */
    public function getEmails()
    {
        return $this->_emails;
    }

    /**
     * Emails setter
     */
    public function setEmails($emails)
    {
        if (!$emails) {
            $emails = [];
        }
        $this->_emails = $emails;
    }

    /**
     * @inheritDoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), ['emails']);
    }
}