<?php
namespace CG\Controllers\App\Service;

use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Http\Exception as HttpException;
use HttpException\Conflict
use CG\Http\StatusCode;
use UnexpectedValueException;

class Collection
{
    use ControllerTrait;

    public function get()
    {
        try {
            return $this->getService()->fetchAllAsHalModel(
                $this->getUrls()
            );
        } catch (NotFound $exception) {
            throw new HttpException\NotFound(
                'No Services Found',
                HttpException\NotFound::HTTP_CODE,
                $exception
            );
        }
    }

    public function post($data)
    {
        try {
            $this->getResponse()->setStatusCode(StatusCode::CREATED);
            return $this->getService()->toHalModel(
                $this->getUrls(),
                $this->getService()->insertFromHal($data)
            );
        } catch (UnexpectedValueException $exception) {
            throw new HttpException\Conflict(
                'Attempting to update Entity',
                StatusCode::CONFLICT,
                $exception
            );
        }
    }
}
