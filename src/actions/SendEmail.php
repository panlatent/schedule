<?php

namespace panlatent\schedule\actions;

use Craft;
use craft\helpers\App;
use panlatent\craft\actions\abstract\Action;
use panlatent\craft\actions\abstract\ContextInterface;

class SendEmail extends Action
{
    public static function displayName(): string
    {
        return Craft::t('schedule', 'Send Email');
    }

    public array $emails = [];

    public string $subject = '';

    public string $message = <<<TWIG
Hello {{name}},


TWIG;

    public function execute(ContextInterface $context): bool
    {
        $emails = [];
        foreach ($this->emails as $email) {
            if ($email['name']) {
                $emails[App::parseEnv($email['email'])] = $email['name'];
            } else {
                $emails[] = App::parseEnv($email['email']);
            }
        }

        $subject = Craft::$app->view->renderString($this->subject, [
            'action' => $this,
        ]);

        $message = Craft::$app->view->renderString($this->message, [
            'action' => $this,
        ]);

        return Craft::$app->mailer
            ->compose()
            ->setTo($emails)
            ->setHtmlBody($message)
            ->setSubject($subject)
            ->send();
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('schedule/_components/actions/SendEmail/settings', [
            'action' => $this,
        ]);
    }
}