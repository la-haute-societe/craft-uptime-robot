<?php
/**
 * UptimeRobot plugin for Craft CMS 3.x
 *
 * Connect your Craft CMS sites to Uptime Robot monitoring service.
 *
 * @link      https://www.lahautesociete.com
 * @copyright Copyright (c) 2018 La Haute Société
 */

namespace lhs\uptimerobot\console\controllers;

use lhs\uptimerobot\UptimeRobot;

use Craft;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * ShowMonitorsStatus Command
 *
 * The first line of this class docblock is displayed as the description
 * of the Console Command in ./craft help
 *
 * Craft can be invoked via commandline console by using the `./craft` command
 * from the project root.
 *
 * Console Commands are just controllers that are invoked to handle console
 * actions. The segment routing is plugin-name/controller-name/action-name
 *
 * The actionIndex() method is what is executed if no sub-commands are supplied, e.g.:
 *
 * ./craft uptime-robot/show-monitors-status
 *
 * Actions must be in 'kebab-case' so actionDoSomething() maps to 'do-something',
 * and would be invoked via:
 *
 * ./craft uptime-robot/show-monitors-status/do-something
 *
 * @author    La Haute Société
 * @package   UptimeRobot
 * @since     1.0.0
 */
class ShowMonitorsStatusController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Handle uptime-robot/show-monitors-status console commands
     *
     * The first line of this method docblock is displayed as the description
     * of the Console Command in ./craft help
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $result = 'something';

        echo "Welcome to the console ShowMonitorsStatusController actionIndex() method\n";

        return $result;
    }

    /**
     * Handle uptime-robot/show-monitors-status/do-something console commands
     *
     * The first line of this method docblock is displayed as the description
     * of the Console Command in ./craft help
     *
     * @return mixed
     */
    public function actionDoSomething()
    {
        $result = 'something';

        echo "Welcome to the console ShowMonitorsStatusController actionDoSomething() method\n";

        return $result;
    }
}
