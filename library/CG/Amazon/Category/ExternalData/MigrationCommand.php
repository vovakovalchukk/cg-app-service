<?php
namespace CG\Amazon\Category\ExternalData;

use CG\Product\Category\Collection as Categories;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\Service as CategoryService;
use CG\Stdlib\Exception\Runtime\NotFound;

class MigrationCommand
{
    protected $migrationStorage;
    protected $categoryService;

    public function __construct(StorageInterface $migrationStorage, CategoryService $categoryService)
    {
        $this->migrationStorage = $migrationStorage;
        $this->categoryService = $categoryService;
    }

    public function migrate()
    {
        $page = 1;
        while (!is_null($categories = $this->fetchCategories($page))) {
//            print_r($categories);

            echo $page . "\n";

            $page++;
        }
    }

    protected function fetchCategories($page): ?Categories
    {
        try {
            $filter = (new CategoryFilter())
                ->setLimit('100')
                ->setPage($page)
                ->setChannel(['amazon'])
                ->setVersion([1]);

            return $this->categoryService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            echo $e->getMessage() . "\n";
            return null;
        }
    }
}