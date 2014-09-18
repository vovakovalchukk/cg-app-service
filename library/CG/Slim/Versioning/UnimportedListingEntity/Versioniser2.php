<?php
namespace CG\Slim\Versioning\UnimportedListingEntity;

use CG\Account\Client\StorageInterface as AccountClient;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser2 implements VersioniserInterface
{
    protected $accountClient;

    public function __construct(AccountClient $accountClient)
    {
        $this->setAccountClient($accountClient);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        
        if (!isset($data['channel'])) {
            if (isset($data['accountId'])) {
                try {
                    $account = $this->getAccountClient()->fetch($data['accountId']);
                    $data['channel'] = $account->getChannel();
                } catch (NotFound $exception) {
                    // Entity not found so no information to copy
                }
            }
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['channel']);
        $response->setData($data);
    }

    protected function getAccountClient()
    {
        return $this->accountClient;
    }

    protected function setAccountClient($accountClient)
    {
        $this->accountClient = $accountClient;
        return $this;
    }
}
