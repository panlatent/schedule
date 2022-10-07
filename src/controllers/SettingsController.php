<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\controllers;

use Craft;
use craft\web\Controller;
use panlatent\schedule\Plugin;
use yii\web\Response;

/**
 * Class SettingsController
 *
 * @package panlatent\schedule\controllers
 * @author Panlatent <panlatent@gmail.com>
 */
class SettingsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        $this->requireAdmin();
    }

    /**
     * @return Response|null
     */
    public function actionSaveGeneral()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $settings = Plugin::$plugin->getSettings();
        $settings->load($request->getBodyParams(), '');

        if (!Craft::$app->getPlugins()->savePluginSettings(Plugin::$plugin, $settings->toArray())) {
            Craft::$app->getSession()->setError(Craft::t('schedule', 'Couldn’t save settings.'));

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('schedule', 'Settings saved.'));

        return $this->redirectToPostedUrl();
    }
}