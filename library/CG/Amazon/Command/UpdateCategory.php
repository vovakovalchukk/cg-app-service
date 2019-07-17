<?php
namespace CG\Amazon\Command;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\Product\Category\Collection as Categories;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\Service as CategoryService;
use CG\Product\Category\VersionMap\Entity as VersionMap;
use CG\Product\Category\VersionMap\Mapper as VersionMapMapper;
use CG\Product\Category\VersionMap\Service as VersionMapService;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCategory
{
    const VERSION = 1;
    const CHANNEL_NAME_AMAZON = 'amazon';

    protected $categoryService;
    protected $versionMapService;
    protected $versionMapMapper;

    public function __construct(CategoryService $categoryService, VersionMapService $versionMapService, VersionMapMapper $versionMapMapper)
    {
        $this->categoryService = $categoryService;
        $this->versionMapService = $versionMapService;
        $this->versionMapMapper = $versionMapMapper;
    }

    public function addCategoryVersion(int $parentCategoryId, OutputInterface $output): array
    {
        $marketplaces = [];
        try {
            $parentCategory = $this->fetchParentCategory($parentCategoryId);

            $output->writeln($parentCategory->getTitle(). ' ' . $parentCategory->getMarketplace());

            $this->saveCategoryWithNewVersion($parentCategory);
            $marketplaces[$parentCategory->getMarketplace()] = $parentCategory->getMarketplace();
        } catch (NotFound $e) {
            return [];
        }
        $this->updateCategories([$parentCategory->getId()]);

        return $marketplaces;
    }

    public function addCategoryVersionMap(array $countryCodes): void
    {
        $channelVersionMaps = $this->createVersionMaps($countryCodes);
        $this->versionMapService->save($channelVersionMaps);
    }

    protected function updateCategories(array $categoryIds): bool
    {
        $childCategoryIds = [];
        foreach ($categoryIds as $categoryId) {
            try {
                $childCategories = $this->fetchCategories($categoryId);
                if ($childCategories->count() <= 0) {
                    throw new NotFound('Child categories have not been found');
                }

                $this->saveCategoriesWithNewVersion($childCategories);
                $childCategoryIds = array_merge($childCategories->getIds(), $childCategoryIds);
            } catch (NotFound $e) {
                //no-op
            }
        }

        if (empty($childCategoryIds)) {
            return false;
        }

        $childCategories = null;
        $this->updateCategories($childCategoryIds);
        return true;
    }

    protected function fetchParentCategory(int $parentCategoryId): Category
    {
        return $this->categoryService->fetch($parentCategoryId);
    }

    protected function fetchCategories(int $categoryId): Categories
    {
        $filter = (new CategoryFilter())->setLimit('all')->setParentId([$categoryId]);
        return $this->categoryService->fetchCollectionByFilter($filter);
    }

    protected function saveCategoriesWithNewVersion(Categories $categories): void
    {
        foreach ($categories as $category) {
            $this->saveCategoryWithNewVersion($category);
        }
    }

    protected function saveCategoryWithNewVersion(Category $category): void
    {
        try {
            $category->setVersion(static::VERSION);
            $this->categoryService->save($category);
        } catch (Conflict | NotModified $e) {
            echo $e->getMessage()."\n";
        }
    }

    protected function createVersionMap(string $countryCode): array
    {
        return [
            'channel' => static::CHANNEL_NAME_AMAZON,
            'marketplace' => $countryCode,
            'accountId' => null,
            'version' => static::VERSION
        ];
    }

    protected function createVersionMaps(array $countryCodes): VersionMap
    {
        $versionMaps = [];
        foreach ($countryCodes as $countryCode) {
            $versionMaps[] = $this->createVersionMap($countryCode);
        }

        return $this->versionMapMapper->fromArray(
            [
                'versionMap' => $versionMaps
            ]
        );
    }
}