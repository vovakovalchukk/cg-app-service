<?php
namespace CG\Amazon\Category\ExternalData;

interface StorageInterface
{
    public function fetch(int $categoryId): Data;
    public function save(int $categoryId, Data $data): void;
    public function remove(int $categoryId): void;
}