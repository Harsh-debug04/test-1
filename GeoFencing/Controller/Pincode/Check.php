<?php
namespace AgriCart\GeoFencing\Controller\Pincode;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use AgriCart\GeoFencing\Helper\Data as GeoFencingHelper;
use Psr\Log\LoggerInterface;

class Check extends Action
{
    protected $resultJsonFactory;
    protected $productRepository;
    protected $request;
    protected $helper;
    protected $logger;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        GeoFencingHelper $helper,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->helper = $helper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $pincode = $this->request->getParam('pincode');
        $productId = $this->request->getParam('product_id');

        if (!$pincode || !$productId) {
            return $result->setData(['success' => false, 'message' => 'Pincode and Product ID are required.']);
        }

        try {
            $product = $this->productRepository->getById($productId);
            $locationString = $product->getGeoLocation();

            if (!$product->getGeofencingEnable() || !$locationString) {
                return $result->setData(['success' => true, 'message' => 'Shipping available.']);
            }

            $productLocation = $this->helper->parseLocation($locationString);
            if (!$productLocation) {
                $productLocation = $this->helper->getCoordinatesForLocation($locationString);
            }

            if (!$productLocation) {
                $this->logger->warning('GeoFencing: Could not geocode product location.', ['location' => $locationString]);
                return $result->setData(['success' => false, 'message' => 'Could not determine product location. Please check the configured location in the admin panel.']);
            }

            $pincodeCoords = $this->helper->getCoordinatesForLocation($pincode);
            if (!$pincodeCoords) {
                return $result->setData(['success' => false, 'message' => 'Could not find location for the entered pincode.']);
            }

            $distance = $this->getDistance(
                $productLocation['lat'],
                $productLocation['lng'],
                $pincodeCoords['lat'],
                $pincodeCoords['lng']
            );

            $fenceRadius = $this->helper->getFenceRadius();

            if ($distance <= $fenceRadius) {
                return $result->setData(['success' => true, 'message' => 'Product is available in your area.']);
            } else {
                return $result->setData(['success' => false, 'message' => 'Product is not available in your area.']);
            }

        } catch (\Exception $e) {
            $this->logger->critical('GeoFencing Pincode Check Error: ' . $e->getMessage());
            return $result->setData(['success' => false, 'message' => 'An error occurred while checking pincode.']);
        }
    }

    private function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
