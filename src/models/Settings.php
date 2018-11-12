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

use Craft;
use craft\base\Model;
use lhs\uptimerobot\UptimeRobot;

/**
 * Uptime Robot Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * @author    La Haute Société
 * @package   UptimeRobot
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some field model attribute
     *
     * @var string
     */
    public $apiKey = '';

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['apiKey', 'required'],
            ['apiKey', 'string']
        ];
    }

    public function afterValidate()
    {
        $uptimeRobotPlugin = UptimeRobot::getInstance();
        if (!$uptimeRobotPlugin->service->checkConnection($this->apiKey)) {
            $this->addError('apiKey', Craft::t(
                'uptime-robot',
                'Could not connect to the Uptime Robot API. Please check the apiKey.'
            ));
        }
    }
}
