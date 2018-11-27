<?php

namespace lhs\uptimerobot\helpers;

use Craft;
use craft\helpers\ArrayHelper;
use lhs\uptimerobot\models\Contact;
use lhs\uptimerobot\models\Monitor;

/**
 * Some display helpers to use with UptimeRobot API
 *
 * @author albanjubert
 */
class UptimeRobotHelper
{
    /**
     * Return a Bootstrap 3 label class according to the given status value
     * @param integer $status
     * @return string
     */
    public static function getMonitorStatusLabelClass($status): string
    {
        $labelClass = [
            Monitor::STATUS_PAUSED          => 'status orange',
            Monitor::STATUS_NOT_CHECKED_YET => 'status yellow',
            Monitor::STATUS_UP              => 'status green',
            Monitor::STATUS_SEEMS_DOWN      => 'status purple',
            Monitor::STATUS_DOWN            => 'status red',
        ];
        return ArrayHelper::getValue($labelClass, $status, 'status grey');
    }

    /**
     * Return the status label according to the given status value
     * @param integer $status
     * @return string
     */
    public static function getMonitorStatusLabel($status): string
    {
        return ArrayHelper::getValue(self::getMonitorStatusLabels(), $status, Craft::t('uptime-robot', 'Unknown'));
    }

    /**
     * Return the list of available status labels indexed by their status value
     * @return array
     */
    public static function getMonitorStatusLabels(): array
    {
        return [
            Monitor::STATUS_PAUSED          => Craft::t('uptime-robot', 'Paused'),
            Monitor::STATUS_NOT_CHECKED_YET => Craft::t('uptime-robot', 'Not checked yet'),
            Monitor::STATUS_UP              => Craft::t('uptime-robot', 'Up'),
            Monitor::STATUS_SEEMS_DOWN      => Craft::t('uptime-robot', 'Seems down'),
            Monitor::STATUS_DOWN            => Craft::t('uptime-robot', 'Down'),
        ];
    }

    /**
     * Format the ratio value to be displayed as percent string
     * @param string $ratio
     * @return string
     */
    public static function displayMonitorRatioAsPercent($ratio): string
    {
        return Craft::$app->formatter->asPercent((float)$ratio / 100, 2);
    }

    /**
     * Return the type label according to the given type value
     * @param integer $type
     * @return string
     */
    public static function getMonitorTypeLabel($type): string
    {
        return ArrayHelper::getValue(self::getMonitorTypeLabels(), $type, Craft::t('uptime-robot', 'Unknown'));
    }

    /**
     * Return the list of available monitor types indexed by their type value
     * @return array
     */
    public static function getMonitorTypeLabels(): array
    {
        return [
            Monitor::TYPE_HTTP    => Craft::t('uptime-robot', 'HTTP'),
            Monitor::TYPE_KEYWORD => Craft::t('uptime-robot', 'Keyword'),
            Monitor::TYPE_PING    => Craft::t('uptime-robot', 'Ping'),
            Monitor::TYPE_PORT    => Craft::t('uptime-robot', 'Port'),
        ];
    }

    /**
     * Return the sub-type label according to the given sub-type value
     * @param integer $subtype
     * @return string
     */
    public static function getMonitorSubTypeLabel($subtype): string
    {
        return ArrayHelper::getValue(self::getMonitorSubTypeLabels(), $subtype, Craft::t('uptime-robot', 'Unknown'));
    }

    /**
     * Return the list of available monitor sub-type (for port monitoring) indexed by their subtype value
     * @return array
     */
    public static function getMonitorSubTypeLabels(): array
    {
        return [
            Monitor::SUBTYPE_HTTP   => Craft::t('uptime-robot', 'HTTP (80)'),
            Monitor::SUBTYPE_HTTPS  => Craft::t('uptime-robot', 'HTTPS (443)'),
            Monitor::SUBTYPE_FTP    => Craft::t('uptime-robot', 'FTP (21)'),
            Monitor::SUBTYPE_SMTP   => Craft::t('uptime-robot', 'SMTP (25)'),
            Monitor::SUBTYPE_POP3   => Craft::t('uptime-robot', 'POP3 (110)'),
            Monitor::SUBTYPE_IMAP   => Craft::t('uptime-robot', 'IMAP (143)'),
            Monitor::SUBTYPE_CUSTOM => Craft::t('uptime-robot', 'Custom port'),
        ];
    }

    /**
     * Return the keyword type label according to the given keyword type value
     * @param integer $keywordType
     * @return string
     */
    public static function getMonitorKeywordTypeLabel($keywordType): string
    {
        return ArrayHelper::getValue(self::getMonitorKeywordTypeLabels(), $keywordType, Craft::t('uptime-robot', 'Unknown'));
    }

    /**
     * Return an array of every possible keyword types indexed by their type value
     * @return array
     */
    public static function getMonitorKeywordTypeLabels(): array
    {
        return [
            Monitor::KEYWORD_TYPE_EXISTS     => Craft::t('uptime-robot', 'Exists'),
            Monitor::KEYWORD_TYPE_NOT_EXISTS => Craft::t('uptime-robot', 'Not Exists')
        ];
    }

    /**
     * Return the log type label according to the given keyword type value
     * @param integer $logtype
     * @return string
     */
    public static function getMonitorLogTypeLabel($logtype): string
    {
        return ArrayHelper::getValue(self::getMonitorLogTypeLabels(), $logtype, Craft::t('uptime-robot', 'Unknown'));
    }

    /**
     * Return an array of every possible log types indexed by their type value
     * @return array
     */
    public static function getMonitorLogTypeLabels(): array
    {
        return [
            Monitor::LOG_TYPE_DOWN    => Craft::t('uptime-robot', 'Down'),
            Monitor::LOG_TYPE_UP      => Craft::t('uptime-robot', 'Up'),
            Monitor::LOG_TYPE_PAUSED  => Craft::t('uptime-robot', 'Paused'),
            Monitor::LOG_TYPE_STARTED => Craft::t('uptime-robot', 'Started'),
        ];
    }

    /**
     * Return a Bootstrap 3 label class according to the given log type value
     * @param integer $logtype
     * @return string
     */
    public static function getMonitorLogTypeLabelClass($logtype): string
    {
        $labelClass = [
            Monitor::LOG_TYPE_STARTED => 'status orange',
            Monitor::LOG_TYPE_UP      => 'status green',
            Monitor::LOG_TYPE_PAUSED  => 'status grey',
            Monitor::LOG_TYPE_DOWN    => 'status red',
        ];
        return ArrayHelper::getValue($labelClass, $logtype);
    }

    /**
     * Return the alert contact type label according to the given keyword type value
     * @param integer $alertcontactType
     * @return string
     */
    public static function getAlertContactTypeLabel($alertcontactType): string
    {
        return ArrayHelper::getValue(self::getAlertContactTypeLabels(), $alertcontactType, Craft::t('uptime-robot', 'Unknown'));
    }

    /**
     * Return an array of every possible alert contact status indexed by their type value
     * @return array
     */
    public static function getAlertContactTypeLabels(): array
    {
        return [
            Contact::TYPE_SMS        => Craft::t('uptime-robot', 'SMS'),
            Contact::TYPE_EMAIL      => Craft::t('uptime-robot', 'Email'),
            Contact::TYPE_TWITTER    => Craft::t('uptime-robot', 'Twitter'),
            Contact::TYPE_BOXCAR     => Craft::t('uptime-robot', 'Boxcar 2'),
            Contact::TYPE_WEB_HOOK   => Craft::t('uptime-robot', 'Web-Hook'),
            Contact::TYPE_PUSHBULLET => Craft::t('uptime-robot', 'Pushbullet'),
            Contact::TYPE_ZAPIER     => Craft::t('uptime-robot', 'Zapier'),
            Contact::TYPE_PUSHOVER   => Craft::t('uptime-robot', 'Pushover'),
            Contact::TYPE_HIPCHAT    => Craft::t('uptime-robot', 'HipChat'),
            Contact::TYPE_SLACK      => Craft::t('uptime-robot', 'Slack')
        ];
    }

    /**
     * Return an array of every possible alert contact status indexed by their type value
     * @return array
     */
    public static function getAlertContactAvailableApiTypeLabels()
    {
        return [
            Contact::TYPE_EMAIL      => Craft::t('uptime-robot', 'Email'),
            Contact::TYPE_TWITTER    => Craft::t('uptime-robot', 'Twitter'),
            Contact::TYPE_BOXCAR     => Craft::t('uptime-robot', 'Boxcar 2'),
            Contact::TYPE_WEB_HOOK   => Craft::t('uptime-robot', 'Web-Hook'),
            Contact::TYPE_PUSHBULLET => Craft::t('uptime-robot', 'Pushbullet'),
            Contact::TYPE_PUSHOVER   => Craft::t('uptime-robot', 'Pushover')
        ];
    }

    /**
     * Return the alert contact status label according to the given keyword status value
     * @param integer $alertcontactStatus
     * @return string
     */
    public static function getAlertContactStatusLabel($alertcontactStatus): string
    {
        return ArrayHelper::getValue(self::getAlertContactStatusLabels(), $alertcontactStatus, Craft::t('uptime-robot', 'Unknown'));
    }

    /**
     * Return an array of every possible alert contact types indexed by their type value
     * @return array
     */
    public static function getAlertContactStatusLabels(): array
    {
        return [
            Contact::CONTACT_STATUS_NOT_ACTIVATED => Craft::t('uptime-robot', 'Not activated'),
            Contact::CONTACT_STATUS_PAUSED        => Craft::t('uptime-robot', 'Paused'),
            Contact::CONTACT_STATUS_ACTIVE        => Craft::t('uptime-robot', 'Active')
        ];
    }

    /**
     * Return a Bootstrap 3 label class according to the given alert contact status value
     * @param integer $statusvalue
     * @return string
     */
    public static function getAlertContactStatusLabelClass($statusvalue): string
    {
        $labelClass = [
            Contact::CONTACT_STATUS_NOT_ACTIVATED => 'label-default',
            Contact::CONTACT_STATUS_PAUSED        => 'label-warning',
            Contact::CONTACT_STATUS_ACTIVE        => 'label-success'
        ];
        return ArrayHelper::getValue($labelClass, $statusvalue);
    }

    /**
     * Return the given seconds length as formatted duration
     * @param $duration
     * @return string
     */
    public static function getFriendlyDuration(int $duration): string
    {
        return Craft::$app->formatter->asDuration($duration);
    }


    /**
     * Format the ratio value to be diplayed as percent string
     * @param string $ratio
     * @return string
     */
    public static function getRatioAsPercent($ratio): string
    {
        return Craft::$app->formatter->asPercent((float)$ratio / 100, 2);
    }

    /**
     * Format a giver interval in seconds into a friendly output per minutes
     * @param $interval
     * @return string
     */
    public static function getFriendlyInterval($interval): string
    {
        return Craft::t('uptime-robot', 'Every {interval,plural,=1{# minute} other{# minutes}}', ['interval' => round($interval / 60)]);
    }
}
