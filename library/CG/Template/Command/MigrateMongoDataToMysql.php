<?php

namespace CG\Template\Command;
use MongoClient;
use CG\Template\Mapper as TemplateMapper;
use CG\Template\Service as TemplateService;
use CG\Template\Collection as TemplateCollection;
use CG\Template\Entity as TemplateEntity;

class MigrateMongoDataToMysql
{
    protected $mongoClient;
    protected $mapper;
    protected $service;

    public function __construct()
    {

    }

    public function __invoke()
    {
        $collection = $this->migrate();
        $idMap = [];
        foreach ($collection as $transaction) {
            $idMap[$transaction->getMongoId()] = $transaction->getId();
        }

        return count($collection);
    }

    protected function migrate()
    {

    }

    public function rollback()
    {

    }

    protected function getMongoClient(): MongoClient
    {
        return $this->mongoClient;
    }

    protected function setMongoClient(MongoClient $mongoClient): MigrateMongoDataToMysql
    {
        $this->mongoClient = $mongoClient;
        return $this;
    }

    public function getMapper(): TemplateMapper
    {
        return $this->mapper;
    }

    public function setMapper($mapper): MigrateMongoDataToMysql
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getService(): TemplateService
    {
        return $this->service;
    }

    public function setService($service): MigrateMongoDataToMysql
    {
        $this->service = $service;
        return $this;
    }
}