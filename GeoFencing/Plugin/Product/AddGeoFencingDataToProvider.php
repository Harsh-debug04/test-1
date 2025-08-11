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
            return $result;
        }

        $product = $subject->getCurrentProduct();
        $key = $product ? $product->getId() : null;

        // If key is null, it's a new product. The data provider uses an empty string for the key.
        if ($key === null) {
            $key = '';
        }

        // This logic handles both existing products (keyed by ID) and new products (keyed by '').
        if (isset($result[$key])) {
            // Ensure the 'product' sub-array exists before trying to modify it.
            if (!isset($result[$key]['product']) || !is_array($result[$key]['product'])) {
                $result[$key]['product'] = [];
            }
            $result[$key]['product']['geofencing']['apiKey'] = $apiKey;
        } elseif (!empty($result) && is_array($result)) {
            // Fallback for cases where the result is not keyed by product ID.
            // This might happen with complex product types or customizations.
            $firstProductKey = array_key_first($result);
            if ($firstProductKey !== null) {
                if (!isset($result[$firstProductKey]['product']) || !is_array($result[$firstProductKey]['product'])) {
                    $result[$firstProductKey]['product'] = [];
                }
                $result[$firstProductKey]['product']['geofencing']['apiKey'] = $apiKey;
            }
        } else {
            // This handles the edge case where the result is empty for a new product form.
            // We create the necessary structure.
            $result[$key]['product']['geofencing']['apiKey'] = $apiKey;
        }

        return $result;
    }
}
