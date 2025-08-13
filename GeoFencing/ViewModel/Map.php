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

    public function isMapVisible()
    {
        $product = $this->getCurrentProduct();
        if (!$product || !$this->helper->isEnabled() || !$this->helper->isShowMap()) {
            return false;
        }
        return (bool)$product->getGeofencingEnable() && $product->getGeoLocation();
    }

    public function getMapConfigJson()
    {
        $product = $this->getCurrentProduct();
        $locationString = $product->getGeoLocation();

        if ($locationString && !$this->helper->parseLocation($locationString)) {
            $coords = $this->helper->getCoordinatesForLocation($locationString);
            if ($coords) {
                $locationString = sprintf('%s (%f, %f)', $locationString, $coords['lat'], $coords['lng']);
            }
        }

        $config = [
            'apiKey' => $this->helper->getGoogleApiKey(),
            'location' => $locationString,
            'radius' => (int)$this->helper->getFenceRadius()
        ];
        return $this->json->serialize($config);
    }

    private function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getProductId()
    {
        $product = $this->getCurrentProduct();
        return $product ? $product->getId() : null;
    }
}