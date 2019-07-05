<?php
namespace CG\Amazon\Command;

use CG\Product\Category\Collection as Categories;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\Service as CategoryService;
use CG\Stdlib\Exception\Runtime\NotFound;

class UpdateCategory
{
    const VERSION = 1;

    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function __invoke($parentCategoryId): bool
    {
        try {
            $categories = $this->fetchCategories($parentCategoryId);
        } catch (NotFound $e) {
            return false;
        }
        $this->updateCategories($categories);
        return true;
    }

    protected function updateCategories(Categories $categories): void
    {
        /* @var $category \CG\Product\Category\Entity */
        foreach ($categories as $category) {
            try {

                echo $category->getTitle()."\n";

                $this->saveCategoryWithNewVersion($category);

                $childCategories = $this->fetchCategories($category->getId());
                if ($childCategories->count() <= 0) {
                    throw new NotFound('Child categories have not been found');
                }

                $this->updateCategories($childCategories);
            } catch (NotFound $e) {
                echo $e->getMessage()."\n";
            }
        }
    }

    protected function fetchCategories(int $categoryId): Categories
    {
        $filter = (new CategoryFilter())->setLimit('all')->setParentId([$categoryId]);
        return $this->categoryService->fetchCollectionByFilter($filter);
    }

    protected function saveCategoryWithNewVersion(Category $category): void
    {
        $category->setVersion(static::VERSION);
//        $this->categoryService->save($category);
    }
}