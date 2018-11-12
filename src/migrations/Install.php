<?php
/**
 * GTmetrix plugin for Craft CMS 3.x
 *
 * GTmetrix gives you insight on how well your entries load and provides actionable recommendations on how to optimise them.
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2017 Luke Youell
 */

namespace lhs\uptimerobot\migrations;

use Craft;
use craft\db\Migration;
use lhs\uptimerobot\records\UptimeRobotMonitor;

/**
 * @author    La Haute Société
 * @copyright Copyright (c) 2018 La Haute Société
 * @link      https://www.lahautesociete.com
 * @package   UptimeRobot
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            // Refresh the db schema caches
            $this->addForeignKeys();
            Craft::$app->db->schema->refresh();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema(UptimeRobotMonitor::UPTIMEROBOT_MONITOR_TABLENAME);
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                UptimeRobotMonitor::UPTIMEROBOT_MONITOR_TABLENAME,
                [
                    'id'                   => $this->primaryKey(),
                    'dateCreated'          => $this->dateTime()->notNull(),
                    'dateUpdated'          => $this->dateTime()->notNull(),
                    'uid'                  => $this->uid(),
                    'uptimeRobotMonitorId' => $this->integer(),
                    'entryId'              => $this->integer(),
                    'siteId'               => $this->integer()->null(),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists(UptimeRobotMonitor::UPTIMEROBOT_MONITOR_TABLENAME);
    }

    /**
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->createIndex(
            $this->db->getIndexName(
                UptimeRobotMonitor::UPTIMEROBOT_MONITOR_TABLENAME,
                'siteId',
                false
            ),
            UptimeRobotMonitor::UPTIMEROBOT_MONITOR_TABLENAME,
            'siteId',
            false
        );
        $this->addForeignKey(
            $this->db->getForeignKeyName(UptimeRobotMonitor::UPTIMEROBOT_MONITOR_TABLENAME, 'siteId'),
            UptimeRobotMonitor::UPTIMEROBOT_MONITOR_TABLENAME,
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }
}
