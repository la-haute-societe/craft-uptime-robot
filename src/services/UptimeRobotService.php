<?php
/**
 * UptimeRobot plugin for Craft CMS 3.x
 *
 * Connect your Craft CMS sites to Uptime Robot monitoring service.
 *
 * @link      https://www.lahautesociete.com
 * @copyright Copyright (c) 2018 La Haute Société
 */

namespace lhs\uptimerobot\services;

use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use lhs\uptimerobot\exceptions\ApiException;
use yii\caching\Cache;
use yii\caching\TagDependency;
use yii\di\Instance;
use yii\helpers\ReplaceArrayValue;
use yii\helpers\StringHelper;
use yii\helpers\VarDumper;
use yii\httpclient\Client;

/**
 * Uptime Robot Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    La Haute Société
 * @package   UptimeRobot
 * @since     1.0.0
 */
class UptimeRobotService extends Component
{
    public $apiKey;
    protected $baseApiUrl = "https://api.uptimerobot.com/v2";
    public $cache = 'cache';
    public $cacheDuration = 300; // 5 minutes cache by default
    private $_client;


    // Public Methods
    // =========================================================================

    public function __call($name, $params)
    {
        $methods = [
            'newMonitor',
            'editMonitor',
            'deleteMonitor',
            'resetMonitor',
            'newAlertContact',
            'editAlertContact',
            'deleteAlertContact',
        ];
        if (in_array($name, $methods)) {
            if (!strpos($name, "Monitor") === false) {
                $this->cache->delete([__CLASS__, 'getAccountDetails']);
                TagDependency::invalidate($this->cache, __CLASS__ . '_monitors');
            }
            if (!strpos($name, "AlertContact") === false) {
                TagDependency::invalidate($this->cache, __CLASS__ . '_alertcontacts');
            }
            return $this->request($name, $params ? $params[0] : []);
        }
        parent::__call($name, $params);
    }

    public function init()
    {
        parent::init();
        $this->cache = Instance::ensure($this->cache, Cache::class);
    }

    public function getClient()
    {
        if (!$this->_client) {
            $this->_client = new Client([
                'baseUrl' => $this->baseApiUrl
            ]);
        }
        return $this->_client;
    }

    /**
     * Send a request to the API by its method name and parameters
     * @param string $method
     * @param array $params
     * @return array
     * @throws ApiException
     * @throws \yii\httpclient\Exception
     */
    public function request($method, $params = []): array
    {
        $params = ArrayHelper::merge($this->getDefaultParams(), $params);
        Craft::debug('Calling Uptime Robot API ' . $method . ' method with params ' . VarDumper::dumpAsString($this->_logSafeParams($params)), __METHOD__);
        $response = $this->getClient()->post('/' . $method, $params)->setOptions([
            'timeout' => 5, // set timeout to 5 seconds for the case server is not responding
        ])->send();
        $data = $response->getData();
        if (!$response->getIsOk()) {
            throw new ApiException(ArrayHelper::getValue($data, 'error.message', 'Unknown API Error'));
        }
        return $data;
    }

    /**
     * Return account details (max number of monitors that can be added and number of up/down/paused monitors) can be grabbed using this method.
     * @param array $params
     * @param bool $forceRefresh
     * @return array account details
     * @throws ApiException
     * @throws \yii\httpclient\Exception
     */
    public function getAccountDetails($params = [], $forceRefresh = false): array
    {
        $cacheKey = [__CLASS__, 'getAccountDetails'];
        $accountDetails = $this->cache->get($cacheKey);
        if (!is_array($accountDetails) || $forceRefresh) {
            $accountDetails = $this->request('getAccountDetails', $params);
            $this->cache->set($cacheKey, $accountDetails, $this->cacheDuration);
        }
        return $accountDetails;
    }

    /**
     * Return any information on monitors.
     * If the number of monitors exceed 50, multiple API calls will be made to retreive the full list.
     * @link https://uptimerobot.com/api see getMonitors method documentation for parameters description
     * @param array $params
     * @param integer $limit (optional) number of records to be returned by each API call. Default to 50
     * @return array monitors indexed by their ID
     * @throws ApiException
     * @throws \yii\httpclient\Exception
     */
    public function getMonitors($params = [], $limit = 50): array
    {
        $cacheKey = ArrayHelper::merge([__CLASS__, 'getMonitors'], $params);
        $allMonitors = $this->cache->get($cacheKey);

        if (!is_array($allMonitors)) {
            $allMonitors = [];
            $offset = 0;
            do {
                $monitors = $this->request('getMonitors', ArrayHelper::merge($params, ['offset' => $offset, 'limit' => $limit]));
                if ($monitors['stat'] === 'fail') {
                    if (isset($monitors['id']) && $monitors['id'] !== 212) { // No monitors found using the given parameters, not an error
                        Craft::error("Failed to request getMonitors, error {$monitors['id']}: {$monitors['error']['message']}", __METHOD__);
                        throw new ApiException('Failed to request getMonitors: ' . $monitors['error']['message'], $monitors['id']);
                    }
                    break;
                }
                $allMonitors = ArrayHelper::merge($allMonitors, $monitors['monitors']);
                $offset += $monitors['pagination']['limit'];
            } while ($offset < $monitors['pagination']['total']);
            $allMonitors = ArrayHelper::index($allMonitors, 'id');
            $this->cache->set($cacheKey, $allMonitors, $this->cacheDuration, new TagDependency(['tags' => __CLASS__ . '_monitors']));
        }
        return $allMonitors;
    }

    /**
     * Return the list of alert contacts.
     * If the number of contacts exceed 50, multiple API calls will be made to retreive the full list.
     * @link https://uptimerobot.com/api see getAlertContacts method documentation for parameters description
     * @param array $params
     * @param integer $limit (optional) number of records to be returned by each API call. Default to 50
     * @return array alert contacts indexed by their ID
     * @throws ApiException
     * @throws \yii\httpclient\Exception
     */
    public function getAlertContacts($params = [], $limit = 50): array
    {
        $cacheKey = ArrayHelper::merge([__CLASS__, 'getAlertContacts'], $params);
        $allAlertContacts = $this->cache->get($cacheKey);
        if (!is_array($allAlertContacts)) {
            $allAlertContacts = [];
            $offset = 0;
            do {
                $alertContacts = $this->request('getAlertContacts', ArrayHelper::merge($params, ['offset' => $offset, 'limit' => $limit]));
                if ($alertContacts['stat'] == 'fail') {
                    if (isset($alertContacts['id']) && $alertContacts['id'] !== 221) { // No contacts found using the given parameters, not an error
                        Craft::error("Failed to request getAlertContacts, error {$alertContacts['id']}: {$alertContacts['error']['message']}", __METHOD__);
                        throw new ApiException('Failed to request getAlertContacts: ' . $alertContacts['error']['message'], $alertContacts['id']);
                    }
                    break;
                }
                $allAlertContacts = ArrayHelper::merge($allAlertContacts, $alertContacts['alert_contacts']);
                $offset += $alertContacts['limit'];
            } while ($offset < $alertContacts['total']);
            $allAlertContacts = ArrayHelper::index($allAlertContacts, 'id');
            $this->cache->set($cacheKey, $allAlertContacts, $this->cacheDuration, new TagDependency(['tags' => __CLASS__ . '_alertcontacts']));
        }
        return $allAlertContacts;
    }

    /**
     * Return the Alert Contact ID based on the given value (email, phone number, url...)
     * @param string $value
     * @return string|null
     * @throws ApiException
     * @throws \yii\httpclient\Exception
     */
    public function getAlertContactIdByValue($value)
    {
        $alertContacts = ArrayHelper::index($this->getAlertContacts(), 'value');
        if (isset($alertContacts[$value])) {
            return $alertContacts[$value]['id'];
        }
    }

    /**
     * Try to connect to Uptime Robot API using a given apiKey
     * @param $apiKey
     * @return bool
     * @throws \yii\httpclient\Exception
     */
    public function checkConnection($apiKey): bool
    {
        Craft::debug('Checking Uptime Robot API connection', __METHOD__);
        try {
            $response = $this->getAccountDetails(['api_key' => $apiKey], true);
        } catch (ApiException $e) {
            return false;
        }
        return ArrayHelper::getValue($response, 'stat') === 'ok';
    }

    /**
     * Get the default set of parameters to be sent to the Uptime Robot API (apiKey, format)
     * @return array
     */
    public function getDefaultParams(): array
    {
        return [
            'api_key' => $this->apiKey,
            'format'  => 'json',
            'time'    => time() // Cache buster
        ];
    }

    /**
     * Obfuscate sensible API parameters for logging purposes
     * @param array $params
     * @return array
     */
    private function _logSafeParams($params = []): array
    {
        return ArrayHelper::merge($params, ['api_key' => new ReplaceArrayValue(StringHelper::truncate($params['api_key'], 3))]);
    }
}
