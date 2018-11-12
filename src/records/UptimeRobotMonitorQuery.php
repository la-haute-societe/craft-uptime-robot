<?php
/**
 * @link http://www.lahautesociete.com
 * @copyright Copyright (c) 2018 La Haute Société
 */

namespace lhs\uptimerobot\records;

use craft\elements\User;
use lhs\uptimerobot\models\Monitor;
use lhs\uptimerobot\UptimeRobot;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * UptimeRobotMonitorQuery class
 *
 * @author albanjubert
 **/
class UptimeRobotMonitorQuery extends ActiveQuery
{
    public function all($db = null)
    {
        $records = parent::all($db);
        $monitors = UptimeRobot::$plugin->service->getMonitors(['alert_contacts' => true, 'all_time_uptime_ratio' => true, 'monitors' => implode('-', ArrayHelper::getColumn($records, 'uptimeRobotMonitorId'))]);
        foreach ($records as $record) {
            $this->_loadMonitorData($monitors, $record);
            $this->_findAlertContacts($record);
        }
        return $records;
    }

    public function one($db = null)
    {
        $record = parent::one($db);
        $monitors = UptimeRobot::$plugin->service->getMonitors(['alert_contacts' => true, 'all_time_uptime_ratio' => true, 'monitors' => $record->uptimeRobotMonitorId]);
        $this->_loadMonitorData($monitors, $record);
        $this->_findAlertContacts($record);
        return $record;
    }

    /**
     * @param $monitors
     * @param $record
     */
    private function _loadMonitorData($monitors, $record)
    {
        // Load Uptime Robot Monitor Data
        $monitorData = ArrayHelper::getValue($monitors, $record->uptimeRobotMonitorId);
        $monitor = null;
        if ($monitorData !== null) {
            $monitor = new Monitor($monitorData);
        }
        $record->setMonitor($monitor);
    }

    /**
     * @param $record
     * @throws \lhs\uptimerobot\exceptions\ApiException
     * @throws \yii\httpclient\Exception
     */
    private function _findAlertContacts($record)
    {
        if ($record->getMonitor() !== null) {
            $users = [];
            if (!empty($record->getMonitor()->alert_contacts)) {
                $alertContacts = UptimeRobot::$plugin->service->getAlertContacts(['alert_contacts' => implode('-', ArrayHelper::getColumn($record->getMonitor()->alert_contacts, 'id'))]);
                $users = User::find()->email(ArrayHelper::getColumn($alertContacts, 'value'))->all();
            }
            $record->setAlertContacts($users);
        }
    }
}
