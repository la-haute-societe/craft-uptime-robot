<?php
/**
 * UptimeRobot plugin for Craft CMS 3.x
 *
 * Connect your Craft CMS sites to Uptime Robot monitoring service.
 *
 * @link      https://www.lahautesociete.com
 * @copyright Copyright (c) 2018 La Haute Société
 */

namespace lhs\uptimerobot\models;

use craft\base\Model;
use Exception;
use lhs\uptimerobot\UptimeRobot;
use RuntimeException;
use yii\helpers\ArrayHelper;

/**
 * Uptime Robot Monitors Model
 *
 * This is a model used to store Uptime Robot Monitors.
 *
 * @author    La Haute Société
 * @package   UptimeRobot
 * @since     1.0.0
 */
class Monitor extends Model
{
    const SCENARIO_UPDATE = 'update';

    // Constants
    // =========================================================================

    // Status
    const STATUS_PAUSED = 0;
    const STATUS_NOT_CHECKED_YET = 1;
    const STATUS_UP = 2;
    const STATUS_SEEMS_DOWN = 8;
    const STATUS_DOWN = 9;
    // Log types
    const LOG_TYPE_DOWN = 1;
    const LOG_TYPE_UP = 2;
    const LOG_TYPE_PAUSED = 99;
    const LOG_TYPE_STARTED = 98;
    // Types
    const TYPE_HTTP = 1;
    const TYPE_KEYWORD = 2;
    const TYPE_PING = 3;
    const TYPE_PORT = 4;
    // Sub-types
    const SUBTYPE_HTTP = 1;
    const SUBTYPE_HTTPS = 2;
    const SUBTYPE_FTP = 3;
    const SUBTYPE_SMTP = 4;
    const SUBTYPE_POP3 = 5;
    const SUBTYPE_IMAP = 6;
    const SUBTYPE_CUSTOM = 99;
    // Keyword types
    const KEYWORD_TYPE_EXISTS = 1;
    const KEYWORD_TYPE_NOT_EXISTS = 2;

    // Public Properties
    // =========================================================================

    public $id;
    public $friendly_name;
    public $url;
    public $type = self::TYPE_HTTP;
    public $sub_type;
    public $port;
    public $keyword_type;
    public $keyword_value;
    public $http_username;
    public $http_password;
    public $interval = 5;
    public $mwindows;
    public $custom_http_headers;
    public $ignore_ssl_errors;
    public $status;
    public $all_time_uptime_ratio;
    public $average_response_time;
    public $response_times = [];
    public $logs = [];
    public $create_datetime;
    private $_alert_contacts = [];

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [
                ['friendly_name', 'url', 'type'],
                'required'
            ],
            [
                ['interval'],
                'integer',
                'min' => 1,
                'max' => 300
            ],
            [
                ['id'],
                'required',
                'on' => self::SCENARIO_UPDATE
            ],
            [
                ['url'],
                'url',
                'defaultScheme' => 'https',
                'when'          => [$this, 'isNotPingType']
            ],
            [
                ['url'],
                'match',
                'pattern' => '/(^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)$|^(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)$)/i',
                'when'    => [$this, 'isPingType']
            ],
            [
                ['port'],
                'required',
                'when' => [$this, 'isPortType']
            ],
            [
                ['port'],
                'integer'
            ],
            [
                ['type'],
                'in',
                'range' => [
                    self::TYPE_HTTP,
                    self::TYPE_KEYWORD,
                    self::TYPE_PING,
                    self::TYPE_PORT
                ]
            ],
            [
                ['sub_type'],
                'in',
                'range' => [
                    self::SUBTYPE_HTTP,
                    self::SUBTYPE_HTTPS,
                    self::SUBTYPE_FTP,
                    self::SUBTYPE_SMTP,
                    self::SUBTYPE_POP3,
                    self::SUBTYPE_IMAP,
                    self::SUBTYPE_CUSTOM
                ],
                'when'  => [$this, 'isPortType']
            ],
            [
                ['sub_type'],
                'required',
                'when' => [$this, 'isPortType']
            ],
            [
                ['http_username'],
                'required',
                'when' => [$this, 'isHttpPasswordSet']
            ],
            [
                ['keyword_type', 'keyword_value'],
                'required',
                'when' => [$this, 'isKeywordType']
            ],
            //            [
            //                'alert_contacts',
            //                'each',
            //                'rule' => ['lhs\uptimerobot\models\Contact']
            //            ],
            [
                'alert_contacts',
                'default',
                'value' => []
            ],
            [
                ['id', 'status', 'sub_type', 'port', 'type', 'keyword_type', 'keyword_value', 'http_username', 'http_password'],
                'default'
            ]
        ];
    }

    public function isPingType(): bool
    {
        return $this->type === self::TYPE_PING;
    }

    public function isNotPingType(): bool
    {
        return $this->type !== self::TYPE_PING;
    }

    public function isKeywordType(): bool
    {
        return $this->type === self::TYPE_KEYWORD;
    }

    public function isPortType(): bool
    {
        return $this->type === self::TYPE_PORT;
    }

    public function isHttpPasswordSet(): bool
    {
        return !empty($this->http_password);
    }

    /**
     * Create or update the current model data to API
     * @return bool if model was saved
     */
    public function save(): bool
    {

        if (!$this->validate()) {
            return false;
        }

        $service = UptimeRobot::$plugin->service;
        if (empty($this->id)) {
            $method = 'newMonitor';
        } else {
            $method = 'editMonitor';
        }

        try {
            $params = $this->getAttributes();
            // Remove unwanted parameters
            foreach ($params as $paramKey => $paramValue) {
                if ($paramValue === null) {
                    unset($params[$paramKey]);
                }
            }
            $params['alert_contacts'] = implode('-', ArrayHelper::getColumn($this->getAlert_contacts(), 'id'));
            if (array_key_exists('status', $params) && $params['status'] > 1) {
                unset($params['status']);
            }
            foreach (['logs', 'response_times', 'all_time_uptime_ratio', 'create_datetime'] as $paramToRemove) {
                if (array_key_exists($paramToRemove, $params)) {
                    unset($params[$paramToRemove]);
                }
            }
            if ($method === 'editMonitor') {
                unset($params['type']); // Can not change type for an existing monitor
            }
            // Call API method
            $result = $service->{$method}($params);
            if ((isset($result['stat']) && ($result['stat'] === 'fail')) || !isset($result['stat'])) {
                $this->addError('friendly_name', ArrayHelper::getValue($result, 'error.message', 'Unknown API error'));
                return false;
            } else {
                $this->id = $result['monitor']['id'];
                if (isset($result['monitor']['status'])) {
                    $this->status = $result['monitor']['status'];
                }
            }
        } catch (Exception $exc) {
            $this->addError('friendly_name', $exc->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Remove the monitor from Uptime Robot
     * @return bool if the model was deleted
     * @throws Exception
     */
    public function delete(): bool
    {
        $service = UptimeRobot::$plugin->service;
        $params = ['id' => $this->id];
        $result = $service->deleteMonitor($params);
        if ((isset($result['stat']) && ($result['stat'] === 'fail')) || !isset($result['stat'])) {
            throw new RuntimeException(ArrayHelper::getValue($result, 'error.message', 'Unknown API error'), ArrayHelper::getValue($result, 'id', -1));
        }
        return true;
    }

    /**
     * @return array
     */
    public function getAlert_contacts(): array
    {
        return $this->_alert_contacts;
    }

    /**
     * @param array $_alert_contacts
     */
    public function setAlert_contacts(array $_alert_contacts)
    {
        foreach ($_alert_contacts as $idx => $alert_contact) {
            if (!($alert_contact instanceof Contact)) {
                $_alert_contacts[$idx] = new Contact($alert_contact);
            }
        }
        $this->_alert_contacts = $_alert_contacts;
    }
}
