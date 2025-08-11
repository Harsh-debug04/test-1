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

class Data extends AbstractHelper
{
    const XML_PATH_GEOFENCING = 'geofencing/';

    /**
     * @param string $field
     * @param int|null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return (bool)$this->getConfigValue(self::XML_PATH_GEOFENCING . 'general/enable', $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getGoogleApiKey($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_GEOFENCING . 'general/google_api_key', $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isShowMap($storeId = null)
    {
        return (bool)$this->getConfigValue(self::XML_PATH_GEOFENCING . 'frontend/show_map', $storeId);
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getFenceRadius($storeId = null)
    {
        return (int)$this->getConfigValue(self::XML_PATH_GEOFENCING . 'frontend/fence_radius', $storeId);
    }
}