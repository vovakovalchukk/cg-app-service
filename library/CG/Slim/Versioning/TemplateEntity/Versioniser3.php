<?php
namespace CG\Slim\Versioning\TemplateEntity;

use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Template\PaperPage;
use Nocarrier\Hal;
use CG\Template\Service;

class Versioniser3 implements VersioniserInterface
{
    /** @var Service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (isset($data['printPage'], $data['multiPerPage'], $data['paperPage']['measurementUnit'])) {
            return;
        }
        if (!isset($data['id'])) {
            $data['printPage'] = [];
            $data['multiPerPage'] = [];
            $data['paperPage']['measurementUnit'] = PaperPage::DEFAULT_MEASUREMENT_UNIT;
            $request->setData($data);
            return;
        }

        try {
            $template = $this->service->fetch($data['id']);
            $data['printPage'] = $template->getPrintPage()->toArray();
            $data['multiPerPage'] = $template->getMultiPerPage()->toArray();
            $data['paperPage']['measurementUnit'] = $template->getPaperPage()->getMeasurementUnit();
        } catch (NotFound $e) {
            $data['printPage'] = [];
            $data['multiPerPage'] = [];
            $data['paperPage']['measurementUnit'] = PaperPage::DEFAULT_MEASUREMENT_UNIT;
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['printPage'], $data['multiPerPage'], $data['paperPage']['measurementUnit']);
        $response->setData($data);
    }
}