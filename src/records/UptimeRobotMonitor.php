<?php
/**
 * Uptime Robot plugin for Craft CMS 3.x
 *
 * Connect your Craft CMS sites to Uptime Robot monitoring service.
 *
 * @link      https://www.lahautesociete.com
 * @copyright Copyright (c) 2018 La Haute Société
 */

namespace lhs\uptimerobot\records;

use Craft;
use craft\db\ActiveRecord;
use craft\elements\Entry;
use craft\elements\User;
use lhs\uptimerobot\helpers\UptimeRobotHelper;
use lhs\uptimerobot\models\Contact;
use lhs\uptimerobot\models\Monitor;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * UptimeRobotMonitor Record
 *
 * @property int $id
 * @property int $entryId
 * @property int $siteId
 * @property int $uptimeRobotMonitorId
 *
 * @author    La Haute Société
 * @package   UptimeRobot
 * @since     1.0.0
 */
class UptimeRobotMonitor extends ActiveRecord
{


    private $_entry;
    private $_contacts = [];
    private $_monitor;

    // Public Static Methods
    // =========================================================================
    /**
     * @inheritdoc
     */
    const UPTIMEROBOT_MONITOR_TABLENAME = '{{%uptimerobot_monitor}}';

    public static function find()
    {
        return new UptimeRobotMonitorQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%uptimerobot_monitor}}';
    }

    public function rules()
    {
        return [
            [['entryId'], 'required'],
            [
                ['entryId'],
                'craft\validators\UniqueValidator',
                'targetAttribute' => ['entryId', 'siteId'],
                'message'         => Craft::t('uptime-robot', 'That entry is already monitored')
            ],
            [['entries', 'alertContacts'], 'safe']
        ];
    }

    public function afterValidate()
    {
        Craft::debug(VarDumper::dumpAsString($this->getErrors()), __METHOD__);
        $entryError = $this->getFirstError('entryId');
        if ($entryError) {
            $this->addError('entries', Craft::t('uptime-robot', $entryError));
        }
        parent::afterValidate();
    }

    public function beforeSave($insert)
    {
        $alertContacts = [];
        $users = $this->getAlertContacts();
        if (is_array($users)) {
            foreach ($users as $user) {
                $contact = new Contact([
                    'friendly_name' => $user->fullName ?? 'Craft user',
                    'value'         => $user->email,
                    'type'          => Contact::TYPE_EMAIL
                ]);
                if ($contact->save()) {
                    $alertContacts[] = $contact;
                }
            }
        }
        $entry = $this->getEntry();
        $this->_monitor = new Monitor([
            'friendly_name'  => $entry->site->name . ' - ' . $entry->title,
            'type'           => Monitor::TYPE_HTTP,
            'url'            => $entry->url,
            'interval'       => 5 * 60,
            'alert_contacts' => $alertContacts
        ]);
        if ($this->uptimeRobotMonitorId !== null) {
            $this->_monitor->id = $this->uptimeRobotMonitorId;
        }
        if ($this->_monitor->save()) {
            $this->uptimeRobotMonitorId = $this->_monitor->id;
        } else {
            \Craft::debug(VarDumper::dumpAsString($this->_monitor->getErrorSummary(true)), __METHOD__);
            $this->addError('entries', $this->_monitor->getFirstError('friendly_name'));
            return false;
        }
        return parent::beforeSave($insert);
    }

    public function afterDelete()
    {
        if($this->_monitor !== null) {
            $this->_monitor->delete();
        }
        parent::afterDelete();
    }

    public function setEntry(Entry $entry = null)
    {

        if (!is_null($entry)) {
            $this->_entry = $entry;
            $this->entryId = $entry->id;
            $this->siteId = $entry->siteId;
        } else {
            $this->_entry = null;
            $this->entryId = null;
            $this->siteId = null;
        }
    }

    public function getEntry()
    {
        if (is_null($this->_entry) && $this->entryId !== null) {
            $this->_entry = Entry::find()->id($this->entryId)->one();
        }
        return $this->_entry;
    }

    public function setEntries($entries)
    {
        if (is_array($entries)) {
            $this->setEntry(!empty($entries) ? Entry::find()->id($entries[0])->one() : null);
        } else {
            $this->setEntry(null);
        }
    }

    public function setAlertContacts($contacts = [])
    {
        foreach ($contacts as $contact) {
            if (!($contact instanceof User)) {
                $contacts = User::find()->id($contacts)->all();
                break;
            }
        }
        if (empty($contacts)) {
            $contacts = [];
        }
        $this->_contacts = $contacts;
    }

    public function getAlertContacts()
    {
        return $this->_contacts;
    }

    public function getMonitor()
    {
        return $this->_monitor;
    }

    public function setMonitor(Monitor $monitor = null)
    {
        $this->_monitor = $monitor;
    }

    public function getName()
    {
        return ArrayHelper::getValue($this->_monitor, 'friendly_name');
    }

    public function getStatusClass()
    {
        return UptimeRobotHelper::getMonitorStatusLabelClass(ArrayHelper::getValue($this->_monitor, 'status'));
    }

    public function getStatus()
    {
        return UptimeRobotHelper::getMonitorStatusLabel(ArrayHelper::getValue($this->_monitor, 'status'));
    }

    public function getType()
    {
        return UptimeRobotHelper::getMonitorTypeLabel(ArrayHelper::getValue($this->_monitor, 'type'));
    }

    public function getUrl()
    {
        return ArrayHelper::getValue($this->_monitor, 'url');
    }

    public function getUptime()
    {
        return UptimeRobotHelper::displayMonitorRatioAsPercent(ArrayHelper::getValue($this->_monitor, 'all_time_uptime_ratio'));
    }

}

