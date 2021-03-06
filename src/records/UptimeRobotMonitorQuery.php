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
        $this->select = null;
        $records = parent::all($db);
        if (!empty($records)) {
            $monitors = UptimeRobot::$plugin->service->getMonitors(['alert_contacts' => true, 'all_time_uptime_ratio' => true, 'monitors' => implode('-', ArrayHelper::getColumn($records, 'uptimeRobotMonitorId'))]);
            foreach ($records as $record) {
                $this->_loadMonitorData($monitors, $record);
                $this->_findAlertContacts($record);
            }
        }
        return $records;
    }

    public function one($db = null)
    {
        $this->select = null;
        $record = parent::one($db);
        if ($record !== null) {
            $monitors = UptimeRobot::$plugin->service->getMonitors(['alert_contacts' => true, 'logs' => true, 'all_time_uptime_ratio' => true, 'response_times' => true, 'monitors' => $record->uptimeRobotMonitorId]);
            $this->_loadMonitorData($monitors, $record);
            $this->_findAlertContacts($record);
        }
        return $record;
    }

    /**
     * @param $monitors
     * @param $record
     */
    private function _loadMonitorData(array $monitors, $record)
    {
        // Load Uptime Robot Monitor Data
        $monitorData = ArrayHelper::getValue($monitors, ArrayHelper::getValue($record, 'uptimeRobotMonitorId'));
        $monitor = null;
        if ($monitorData !== null) {
            $monitor = new Monitor([
                'id'                    => ArrayHelper::getValue($monitorData, 'id'),
                'friendly_name'         => ArrayHelper::getValue($monitorData, 'friendly_name'),
                'url'                   => ArrayHelper::getValue($monitorData, 'url'),
                'type'                  => ArrayHelper::getValue($monitorData, 'type'),
                'sub_type'              => ArrayHelper::getValue($monitorData, 'sub_type'),
                'port'                  => ArrayHelper::getValue($monitorData, 'port'),
                'keyword_type'          => ArrayHelper::getValue($monitorData, 'keyword_type'),
                'keyword_value'         => ArrayHelper::getValue($monitorData, 'keyword_value'),
                'http_username'         => ArrayHelper::getValue($monitorData, 'http_username'),
                'http_password'         => ArrayHelper::getValue($monitorData, 'http_password'),
                'interval'              => ArrayHelper::getValue($monitorData, 'interval'),
                'mwindows'              => ArrayHelper::getValue($monitorData, 'mwindows'),
                'custom_http_headers'   => ArrayHelper::getValue($monitorData, 'custom_http_headers'),
                'ignore_ssl_errors'     => ArrayHelper::getValue($monitorData, 'ignore_ssl_errors'),
                'status'                => ArrayHelper::getValue($monitorData, 'status'),
                'all_time_uptime_ratio' => ArrayHelper::getValue($monitorData, 'all_time_uptime_ratio'),
                'average_response_time' => ArrayHelper::getValue($monitorData, 'average_response_time'),
                'response_times'        => ArrayHelper::getValue($monitorData, 'response_times'),
                'logs'                  => ArrayHelper::getValue($monitorData, 'logs'),
                'create_datetime'       => ArrayHelper::getValue($monitorData, 'create_datetime'),
                'alert_contacts'        => ArrayHelper::getValue($monitorData, 'alert_contacts'),
            ]);
        }
        if (is_array($record)) {
            $record['monitor'] = $monitor;
        } else {
            $record->setMonitor($monitor);
        }
    }

    /**
     * @param $record
     * @throws \lhs\uptimerobot\exceptions\ApiException
     * @throws \yii\httpclient\Exception
     */
    private function _findAlertContacts($record)
    {
        if (ArrayHelper::getValue($record, 'monitor') !== null) {
            $users = [];
            if (!empty($record->getMonitor()->alert_contacts)) {
                $alertContacts = UptimeRobot::$plugin->service->getAlertContacts(['alert_contacts' => implode('-', ArrayHelper::getColumn($record->getMonitor()->alert_contacts, 'id'))]);
                $users = User::find()->email(ArrayHelper::getColumn($alertContacts, 'value'))->all();
            }
            if (is_array($record)) {
                $record['alertContacts'] = $users;
            } else {
                $record->setAlertContacts($users);
            }

        }
    }
}
