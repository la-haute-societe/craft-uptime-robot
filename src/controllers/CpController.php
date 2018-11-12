<?php
/**
 * Uptime Robot plugin for Craft CMS 3.x
 *
 * Connect your Craft CMS sites to Uptime Robot monitoring service.
 *
 * @link      https://www.lahautesociete.com
 * @copyright Copyright (c) 2018 La Haute Société
 */

namespace lhs\uptimerobot\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use lhs\uptimerobot\records\UptimeRobotMonitor;
use lhs\uptimerobot\UptimeRobot;
use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;

/**
 * CP Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    La Haute Société
 * @package   UptimeRobot
 * @since     1.0.0
 */
class CpController extends Controller
{

    public function beforeAction($action)
    {
        if (empty(UptimeRobot::$plugin->getSettings()->apiKey)) {
            return $this->redirect(UrlHelper::cpUrl('settings/plugins/uptime-robot'));
        }
        return parent::beforeAction($action);
    }

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL
     *
     * @param string $handle
     * @return mixed
     */
    public function actionIndex(string $handle = null)
    {
        $monitors = UptimeRobotMonitor::find()->all();
        return $this->renderTemplate('uptime-robot/cp/index', ['monitors' => $monitors]);
    }


    public function actionAddMonitor(string $handle = null)
    {
        if ($handle !== null) {
            Craft::$app->sites->setCurrentSite(Craft::$app->sites->getSiteByHandle($handle));
        }
        $model = new UptimeRobotMonitor(['siteId' => Craft::$app->sites->getCurrentSite()->id]);
        if ($model->load(Craft::$app->getRequest()->post()) && $model->save()) {
            Craft::$app->getSession()->setNotice(Craft::t(
                'uptime-robot',
                'Monitor has been successfully added.'
            ));
            return $this->redirect(UrlHelper::cpUrl('uptime-robot'));
        }
        Craft::debug('Errors: ' . VarDumper::dumpAsString($model->getErrorSummary(true)), __METHOD__);
        return $this->renderTemplate('uptime-robot/cp/add-monitor', ['model' => $model]);
    }

    public function actionEditMonitor($id)
    {
        $model = UptimeRobotMonitor::findOne($id);
        Craft::$app->sites->setCurrentSite($model->siteId);
        if ($model->load(Craft::$app->getRequest()->post()) && $model->save()) {
            Craft::$app->getSession()->setNotice(Craft::t(
                'uptime-robot',
                'Monitor has been successfully updated.'
            ));
            return $this->redirect(UrlHelper::cpUrl('uptime-robot'));
        }
        Craft::debug('Errors: ' . VarDumper::dumpAsString($model->getErrorSummary(true)), __METHOD__);
        return $this->renderTemplate('uptime-robot/cp/edit-monitor', ['model' => $model]);
    }

    public function actionRemoveMonitor()
    {
        $this->requirePostRequest();
        $id = Craft::$app->getRequest()->getRequiredBodyParam('id');
        $model = UptimeRobotMonitor::findOne($id);
        if(!$model) {
            throw new NotFoundHttpException('The requested monitor does not exist.');
        }
        Craft::$app->sites->setCurrentSite($model->siteId);
        if ($model->delete()) {
            Craft::$app->getSession()->setNotice(Craft::t(
                'uptime-robot',
                'Monitor has been successfully deleted.'
            ));
        } else {
            Craft::$app->getSession()->setError(Craft::t(
                'uptime-robot',
                'An error occured while deleting the monitor.'
            ));
        }
        return $this->redirect(UrlHelper::cpUrl('uptime-robot'));
    }
}
