<?php
/**
 * Uptime Robot plugin for Craft CMS 3.x
 *
 * Connect your Craft CMS sites to Uptime Robot monitoring service.
 *
 * @link      https://www.lahautesociete.com
 * @copyright Copyright (c) 2018 La Haute Société
 */

namespace lhs\uptimerobot\variables;

use craft\elements\Entry;
use craft\elements\User;
use lhs\uptimerobot\helpers\UptimeRobotHelper;

/**
 * Uptime Robot Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.uptimeRobot }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    La Haute Société
 * @package   UptimeRobot
 * @since     1.0.0
 */
class UptimeRobotVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.uptimeRobot.exampleVariable }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.uptimeRobot.exampleVariable(twigValue) }}
     *
     * @param null $optional
     * @return string
     */
    public function entryType()
    {
        return Entry::class;
    }

    public function userType()
    {
        return User::class;
    }

    public function helper($method, $params = null)
    {
        return $params === null ? UptimeRobotHelper::$method($params) : UptimeRobotHelper::$method();
    }
}
