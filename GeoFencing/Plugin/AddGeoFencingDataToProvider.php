<?php
/**
 * AgriCart GeoFencing Module
 *
 * @category    AgriCart
 * @package     AgriCart_GeoFencing
 * @author      AgriCart
 * @copyright   Copyright (c) 2025 AgriCart
 */
namespace AgriCart\GeoFencing\Plugin\Product;

use AgriCart\GeoFencing\Helper\Data as GeoFencingHelper;
use Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider;

class AddGeoFencingDataToProvider
{
    protected $helper;

    public function __construct(GeoFencingHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Adds the Google API key to the product form's data provider.
     * This method safely handles both new and existing products.
     *
     * @param ProductDataProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetData(ProductDataProvider $subject, $result)
    {
        $apiKey = $this->helper->getGoogleApiKey();
        if (empty($apiKey)) {
            return $result; // Do nothing if the API key isn't configured.
        }

        // For existing products, the key is the product ID. For new products, it's often empty.
        $key = $subject->getCurrentProduct() ? $subject->getCurrentProduct()->getId() : '';

        if (isset($result[$key])) {
             $result[$key]['geofencing']['apiKey'] = $apiKey;
        } elseif (!empty($result) && is_array($result)) {
             // Fallback for new products: inject the API key into the first available data set.
             $firstProductKey = array_key_first($result);
             if ($firstProductKey !== null) {
                $result[$firstProductKey]['geofencing']['apiKey'] = $apiKey;
             }
        }

        return $result;
    }
}
