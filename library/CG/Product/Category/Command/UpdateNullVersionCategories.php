<?php
namespace CG\Product\Category\Command;

use CG\Product\Category\Entity as Category;
use CG\Product\Category\Filter;
use CG\Product\Category\Service as CategoryService;
use CG\Product\Category\VersionMap\Mapper as VersionMapMapper;
use CG\Product\Category\VersionMap\Service as VersionMapService;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateNullVersionCategories
{
    protected const VERSION = 99999999;
    protected const MAX_RETRIES = 3;

    protected $categoryService;
    protected $versionMapService;
    protected $versionMapMapper;

    public function __construct(CategoryService $categoryService, VersionMapService $versionMapService, VersionMapMapper $versionMapMapper)
    {
        $this->categoryService = $categoryService;
        $this->versionMapService = $versionMapService;
        $this->versionMapMapper = $versionMapMapper;
    }

    public function update(OutputInterface $output, $channelName, $marketplace)
    {
        $filter = (new Filter())
            ->setLimit(50)
            ->setPage(1)
            ->setChannel([$channelName])
            ->setMarketplace([$marketplace]);

        while (true) {
            try {
                $categories = $this->categoryService->fetchCollectionByFilter($filter);

                /* @var Category $category */
                foreach ($categories as $category) {
                    if ($category->getVersion() != null) {
                        continue;
                    }

                    $output->writeln('Updating <fg=green>'.$category->getTitle() .' - '. $category->getMarketplace() .' - '. $category->getId().'</>');

                    $this->saveCategory($output, $category, static::VERSION);

                }

                $filter->setPage($filter->getPage()+1);
            } catch (NotFound $exception) {
                break;
            }

        }
    }

    protected function saveCategory(OutputInterface $output, Category $category, int $version): void
    {
        for ($i = 0; $i <= static::MAX_RETRIES; $i++) {
            try {
                $category->setVersion($version);
                $this->categoryService->save($category);
                return;
            } catch (Conflict $exception) {
                $output->writeln('Retrying '.$i.' <fg=red>'.$category->getTitle() .' - '. $category->getId().' - '. $version .'</>');
                $version++;
            }
        }
    }
}