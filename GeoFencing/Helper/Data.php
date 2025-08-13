<?php
/**
 * AgriCart GeoFencing Module
 *
 * @category    AgriCart
 * @package     AgriCart_GeoFencing
 * @author      AgriCart
 * @copyright   Copyright (c) 2025 AgriCart
 */

namespace AgriCart\GeoFencing\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    const XML_PATH_GEOFENCING = 'geofencing/';

    protected $curl;
    protected $logger;

    public function __construct(
        Context $context,
        Curl $curl,
        LoggerInterface $logger
    ) {
        $this->curl = $curl;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isEnabled($storeId = null)
    {
        return (bool)$this->getConfigValue(self::XML_PATH_GEOFENCING . 'general/enable', $storeId);
    }

    public function getGoogleApiKey($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_GEOFENCING . 'general/google_api_key', $storeId);
    }

    public function isShowMap($storeId = null)
    {
        return (bool)$this->getConfigValue(self::XML_PATH_GEOFENCING . 'frontend/show_map', $storeId);
    }

    public function getFenceRadius($storeId = null)
    {
        return (int)$this->getConfigValue(self::XML_PATH_GEOFENCING . 'frontend/fence_radius', $storeId);
    }

    public function getCoordinatesForLocation($location)
    {
        $apiKey = $this->getGoogleApiKey();
        if (!$apiKey || !$location) {
            return null;
        }
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($location) . '&key=' . $apiKey;

        try {
            $this->curl->get($url);
            $response = json_decode($this->curl->getBody(), true);

            if (isset($response['results'][0]['geometry']['location'])) {
                return $response['results'][0]['geometry']['location'];
            }
        } catch (\Exception $e) {
            $this->logger->error('GeoFencing: Error fetching coordinates from Google Maps API. ' . $e->getMessage());
        }

        return null;
    }

    public function parseLocation($locationString)
    {
        $matches = [];
        if (preg_match('/\\(([^)]+)\\)$/', $locationString, $matches)) {
            if (isset($matches[1])) {
                $parts = explode(',', $matches[1]);
                if (count($parts) === 2) {
                    $lat = (float)trim($parts[0]);
                    $lng = (float)trim($parts[1]);

                    if ($lat != 0 && $lng != 0) {
                        return ['lat' => $lat, 'lng' => $lng];
                    }
                }
            }
        }
        return null;
    }
}
