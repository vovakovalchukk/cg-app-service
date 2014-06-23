<?php
namespace CG\Settings\Invoice;

interface StorageInterface
{
    public function fetchCollectionByPagination($limit, $page);
    public function fetch($id);
    public function save($id);
    public function remove($entity);
}