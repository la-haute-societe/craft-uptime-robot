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
use lhs\uptimerobot\models\Monitor;
use lhs\uptimerobot\records\UptimeRobotMonitor;
use lhs\uptimerobot\UptimeRobot;
use yii\helpers\ArrayHelper;
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
     * @return \yii\web\Response
     * @throws \lhs\uptimerobot\exceptions\ApiException
     * @throws \yii\httpclient\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionIndex()
    {
        $this->requirePermission('uptime-robot:view-monitors');
        $monitorsQuery = UptimeRobotMonitor::find();
        if (Craft::$app->getIsMultiSite() === true) {
            $allowedSitesIds = [];
            foreach (Craft::$app->getSites()->getAllSites() as $site) {
                if (Craft::$app->user->checkPermission('uptime-robot:view-monitors:' . $site->id)) {
                    $allowedSitesIds[] = $site->id;
                }
            }
            $monitorsQuery->where(['siteId' => $allowedSitesIds]);
        }
        try {
            $variables['monitors'] = $monitorsQuery->all();
        } catch (\yii\httpclient\Exception $e) {
            return $this->_handleApiError($e);
        }
        $accountInfo = UptimeRobot::$plugin->service->getAccountDetails();
        $monitors = UptimeRobot::$plugin->service->getMonitors();
        $variables['monitorsLeft'] = ArrayHelper::getValue($accountInfo, 'account.monitor_limit', 0) - count($monitors);
        $variables['showAddMonitor'] = $variables['monitorsLeft'] > 0;

        return $this->renderTemplate('uptime-robot/cp/index', $variables);
    }

    /**
     * @param string|null $handle
     * @return \yii\web\Response
     * @throws \craft\errors\MissingComponentException
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionAddMonitor(string $handle = null)
    {
        $this->requirePermission('uptime-robot:add-monitors');
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
        return $this->renderTemplate('uptime-robot/cp/add-monitor', ['model' => $model]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionViewMonitor($id)
    {
        $this->requirePermission('uptime-robot:view-monitor');
        try {
            $model = UptimeRobotMonitor::findOne($id);
        } catch (\yii\httpclient\Exception $e) {
            return $this->_handleApiError($e);
        }
        if (!$model) {
            throw new NotFoundHttpException('The requested monitor does not exist.');
        }
        if (Craft::$app->getIsMultiSite() === true) {
            $this->requirePermission('uptime-robot:view-monitor:' . $model->siteId);
        }
        Craft::$app->sites->setCurrentSite($model->siteId);
        // Check if the monitor still exists
        if ($model->getMonitor() === null) {
            Craft::$app->getSession()->setError(Craft::t(
                'uptime-robot',
                'The related Uptime Robot monitor seems to not exists anymore.'
            ));
            return $this->redirect(UrlHelper::cpUrl('uptime-robot'));
        }
        $variables = ['model' => $model];
        return $this->renderTemplate('uptime-robot/cp/view-monitor', $variables);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEditMonitor($id)
    {
        $this->requirePermission('uptime-robot:edit-monitor');
        try {
            $model = UptimeRobotMonitor::findOne($id);
        } catch (\yii\httpclient\Exception $e) {
            return $this->_handleApiError($e);
        }
        if (!$model) {
            throw new NotFoundHttpException('The requested monitor does not exist.');
        }
        if (Craft::$app->getIsMultiSite() === true) {
            $this->requirePermission('uptime-robot:edit-monitor:' . $model->siteId);
        }
        Craft::$app->sites->setCurrentSite($model->siteId);
        // Check if the monitor still exists
        if ($model->getMonitor() === null) {
            Craft::$app->getSession()->setError(Craft::t(
                'uptime-robot',
                'The related Uptime Robot monitor seems to not exists anymore.'
            ));
            return $this->redirect(UrlHelper::cpUrl('uptime-robot/remove-monitor/' . $id));
        }
        // Check if we can handle that type of monitor
        if ($model->getMonitor()->type !== Monitor::TYPE_HTTP) {
            Craft::$app->getSession()->setError(Craft::t(
                'uptime-robot',
                'That type of monitor cannot be modified by the Uptime Robot plugin .'
            ));
            return $this->redirect(UrlHelper::cpUrl('uptime-robot'));
        }

        if ($model->load(Craft::$app->getRequest()->post()) && $model->save()) {
            Craft::$app->getSession()->setNotice(Craft::t(
                'uptime-robot',
                'Monitor has been successfully updated.'
            ));
            return $this->redirect(UrlHelper::cpUrl('uptime-robot'));
        }
        $variables = ['model' => $model];
        // Check which sub-menu to show or not
        $variables['showSubMenu'] = false;
        $variables['showRemoveMonitor'] = false;
        if (Craft::$app->user->checkPermission('uptime-robot:remove-monitor:' . $model->siteId)) {
            $variables['showSubMenu'] = true;
            $variables['showRemoveMonitor'] = true;
        }
        return $this->renderTemplate('uptime-robot/cp/edit-monitor', $variables);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionRemoveMonitor($id)
    {
        $this->requirePermission('uptime-robot:remove-monitor');
        try {
            $model = UptimeRobotMonitor::findOne($id);
        } catch (\yii\httpclient\Exception $e) {
            return $this->_handleApiError($e);
        }
        if (!$model) {
            throw new NotFoundHttpException('The requested monitor does not exist.');
        }
        if (Craft::$app->getIsMultiSite() === true) {
            $this->requirePermission('uptime-robot:remove-monitor:' . $model->siteId);
        }
        Craft::$app->sites->setCurrentSite($model->siteId);
        if (Craft::$app->getRequest()->getIsPost()) {
            if ($model->delete()) {
                Craft::$app->getSession()->setNotice(Craft::t(
                    'uptime-robot',
                    'Monitor has been successfully deleted.'
                ));
            } else {
                Craft::$app->getSession()->setError(Craft::t(
                    'uptime-robot',
                    'An error occurred while deleting the monitor.'
                ));
            }
            return $this->redirect(UrlHelper::cpUrl('uptime-robot'));
        }

        $variables = ['model' => $model];
        return $this->renderTemplate('uptime-robot/cp/confirm-monitor-removal', $variables);

    }

    /**
     * Log the given exception and redirect to the API error template
     *
     * @param \Exception $e
     * @return \yii\web\Response
     */
    private function _handleApiError(\Exception $e): \yii\web\Response
    {
        Craft::error('An error occured while trying to connect to Uptime Robot API: ' . $e->getMessage(), __METHOD__);
        return $this->renderTemplate('uptime-robot/cp/api-error', ['message' => $e->getMessage()]);
    }
}
