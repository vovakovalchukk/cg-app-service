<?php
namespace CG\Amazon\Category\ExternalData;

use CG\Product\Category\Collection as Categories;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\Service as CategoryService;
use CG\Stdlib\Exception\Runtime\NotFound;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationCommand
{
    protected $migrationStorage;
    protected $categoryService;

    public function __construct(StorageInterface $migrationStorage, CategoryService $categoryService)
    {
        $this->migrationStorage = $migrationStorage;
        $this->categoryService = $categoryService;
    }

    public function __invoke(OutputInterface $output): void
    {
        $page = 1;
        $totalCategories = 0;

        while (!is_null($categories = $this->fetchCategories($page))) {
            /* @var $category \CG\Product\Category\Entity */
            foreach ($categories as $category) {
                $this->fetchExternalData($category->getId());
            }

            $totalCategories += $categories->count();
            $output->write('.');
            $page++;
        }

        $output->writeln('');
        $output->writeln('<fg=green>Completed</>');
        $output->writeln('<fg=cyan>Migrated '.$totalCategories.' categories</>');
    }

    protected function fetchExternalData(int $categoryId): void
    {
        $this->migrationStorage->fetch($categoryId);
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
            return null;
        }
    }
}