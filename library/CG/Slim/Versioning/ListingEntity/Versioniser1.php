<?php
namespace CG\Slim\Versioning\ListingEntity;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Nocarrier\Hal;

class Versioniser1 extends AbstractVersioniser implements LoggerAwareInterface
{
    use LogTrait;

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (isset($data['productIds']) && is_array($data['productIds']))  {
            return;
        }

        if (isset($data['productId'])) {
            $data['productIds'] = [$data['productId']];
            unset($data['productId']);
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        if (
            empty($data['productIds']) ||
            !is_array($data['productIds'])
        )  {
            $this->logError('No productIds in response data for listing %s', [$data['id']], 'Versioniser::ListingEntity');
            return;
        }
        // No way to tell which one should be returned so return first
        $data['productId'] = $data['productIds'][0];
        unset($data['productIds']);
        $response->setData($data);
    }
}
