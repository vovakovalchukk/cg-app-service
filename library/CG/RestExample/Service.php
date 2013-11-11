<?php
namespace CG\RestExample;

use CG\InputValidation\RestExample\Filter as InputValidationRules;
use CG\Stdlib\Exception\Runtime\FieldValidationMessagesException;
use CG\Validation\InputValidator;

class Service implements ServiceInterface
{
    protected $repository;
    protected $mapper;

    /**
     * The general pattern that should be employed here is:
     *  1: Fetch an entity or collection from the repository
     *  2: Use the mapper to convert the entity or collection to Hal.
     */
    public function __construct(Repository $repository, Mapper $mapper)
    {
        $this->setRepository($repository)->setMapper($mapper);
    }

    public function validateInput($di, $input)
    {
        $validationRules = $di->get(InputValidationRules::class);
        $rules = $validationRules->getRules();
        $validator = new InputValidator($rules, $input);

        if (! $validator->isValid()) {
            $e = new FieldValidationMessagesException();
            $e->addErrors($validator->getInvalidInput());
            throw $e;
        }
        return $this;
    }

    protected function fetch($status)
    {
        return $this->getRepository()->fetch($status);
    }

    public function fetchAsHal($status)
    {
        $entity = $this->fetch($status);
        return $this->getMapper()->entityToHal($entity);
    }

    protected function setRepository(Repository $repository)
    {
        $this->repository = $repository;
        return $this;
    }

    protected function getRepository()
    {
        return $this->repository;
    }

    protected function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    protected function getMapper()
    {
        return $this->mapper;
    }
}