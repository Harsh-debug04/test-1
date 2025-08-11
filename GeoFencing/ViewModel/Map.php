<?php
/**
 * AgriCart GeoFencing Module
 *
 * @category    AgriCart
 * @package     AgriCart_GeoFencing
 * @author      AgriCart
 * @copyright   Copyright (c) 2025 AgriCart
 */
namespace AgriCart\GeoFencing\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use AgriCart\GeoFencing\Helper\Data as GeoFencingHelper;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;

class Map implements ArgumentInterface
{
    protected $helper;
    protected $registry;
    protected $json;

    public function __construct(
        GeoFencingHelper $helper,
        Registry $registry,
        Json $json
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
        $this->json = $json;
    }

    /**
     * Determines if the map should be displayed on the product page.
     *
     * @return bool
     */
    public function isMapVisible()
    {
        $product = $this->getCurrentProduct();
        if (!$product || !$this->helper->isEnabled() || !$this->helper->isShowMap()) {
            return false;
        }
        // Map is only visible if the product-specific toggle is "Yes" and a location is saved.
        return (bool)$product->getGeofencingEnable() && $product->getGeoLocation();
    }

    /**
     * Provides all necessary data for the map component as a JSON string.
     *
     * @return string
     */
    public function getMapConfigJson()
    {
        $product = $this->getCurrentProduct();
        $config = [
            'apiKey' => $this->helper->getGoogleApiKey(),
            'location' => $product->getGeoLocation(),
            'radius' => (int)$this->helper->getFenceRadius()
        ];
        return $this->json->serialize($config);
    }

    /**
     * Retrieves the current product from the Magento registry.
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    private function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }
}