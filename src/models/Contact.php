<?php
/**
 * @link http://www.lahautesociete.com
 * @copyright Copyright (c) 2018 La Haute Société
 */

namespace lhs\uptimerobot\models;


use craft\base\Model;
use Exception;
use lhs\uptimerobot\UptimeRobot;
use RuntimeException;
use yii\helpers\ArrayHelper;

/**
 * Contact class
 *
 * @author albanjubert
 **/
class Contact extends Model
{
    // Contact types
    const TYPE_SMS = 1; // Not supported yet
    const TYPE_EMAIL = 2;
    const TYPE_TWITTER = 3;
    const TYPE_BOXCAR = 4;
    const TYPE_WEB_HOOK = 5;
    const TYPE_PUSHBULLET = 6;
    const TYPE_ZAPIER = 7;
    const TYPE_PUSHOVER = 9;
    const TYPE_HIPCHAT = 10;
    const TYPE_SLACK = 11;

    // Contact status
    const STATUS_NOT_ACTIVATED = 0;
    const STATUS_PAUSED = 1;
    const STATUS_ACTIVE = 2;

    const SCENARIO_UPDATE = 'update';

    public $id;
    public $friendly_name;
    public $type = self::TYPE_EMAIL;
    public $value;
    public $status;
    public $threshold;
    public $recurrence;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [
                ['type', 'value'],
                'required'
            ],
            [
                ['id'],
                'required',
                'on' => self::SCENARIO_UPDATE
            ],
            [
                ['friendly_name'],
                'default'
            ],
            [
                ['value'],
                'email',
                'when' => [$this, 'isEmailType']
            ],
            [
                ['value'],
                'match',
                'pattern' => '/^[A-Za-z0-9_]{1,15}$/',
                'when'    => [$this, 'isTwitterType']
            ],
            [
                ['value'],
                'url',
                'defaultScheme' => 'http',
                'when'          => [$this, 'isWebHookType']
            ],
        ];
    }

    public function isEmailType(): bool
    {
        return $this->type === self::TYPE_EMAIL;
    }

    public function isTwitterType(): bool
    {
        return $this->type === self::TYPE_TWITTER;
    }

    public function isWebHookType(): bool
    {
        return $this->type === self::TYPE_WEB_HOOK;
    }

    /**
     * Save the current model data to API
     * @return boolean if model was saved
     * @throws \lhs\uptimerobot\exceptions\ApiException
     * @throws \yii\httpclient\Exception
     */
    public function save(): bool
    {

        if (!$this->validate()) {
            return false;
        }

        $service = UptimeRobot::$plugin->service;
        // Try to find alert contact id based on contactValue
        if (empty($this->id) && !empty($this->value)) {
            $this->id = $service->getAlertContactIdByValue($this->value);
        }
        if (empty($this->id)) {
            $method = 'newAlertContact';
        } else {
            $method = 'editAlertContact';
        }

        try {
            $params = $this->getAttributes();
            // Remove unwanted parameters
            foreach ($params as $paramKey => $paramValue) {
                if ($paramValue === null) {
                    unset($params[$paramKey]);
                }
            }
            foreach (['status', 'threshold', 'recurrence'] as $paramToRemove) {
                if (array_key_exists($paramToRemove, $params)) {
                    unset($params[$paramToRemove]);
                }
            }
            $result = $service->{$method}($params);
            if ((isset($result['stat']) && ($result['stat'] === 'fail')) || !isset($result['stat'])) {
                $this->addError('friendly_name', ArrayHelper::getValue($result, 'error.message', 'Unknown API error'));
                return false;
            }
        } catch (Exception $exc) {
            $this->addError('friendly_name', $exc->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Remove the contact from the Uptime Robot
     * @return bool
     */
    public function delete(): bool
    {
        $service = UptimeRobot::$plugin->service;
        $params = ['id' => $this->id];
        $result = $service->deleteAlertContact($params);
        if ((isset($result['stat']) && ($result['stat'] === 'fail')) || !isset($result['stat'])) {
            throw new RuntimeException(ArrayHelper::getValue($result, 'error.message', 'Unknown API error'), ArrayHelper::getValue($result, 'id', -1));
        }
        return true;
    }
}
