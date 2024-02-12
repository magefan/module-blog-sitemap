<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\BlogSitemap\Model\ItemProvider;

use Magefan\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magefan\BlogSitemap\Model\BlogSitemapItemInterfaceFactory;

class BlogPost implements ItemProviderInterface
{
    /**
     * Blog page factory
     *
     * @var CollectionFactory
     */
    private $postFactory;

    /**
     * BlogSitemap item factory
     *
     * @var BlogSitemapItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * Config reader
     *
     * @var ConfigReaderInterface
     */
    private $configReader;

    /**
     * BlogPost constructor.
     *
     * @param ConfigReaderInterface $configReader
     * @param CollectionFactory $postFactory
     * @param BlogSitemapItemInterfaceFactory $itemFactory
     */
    public function __construct(
        ConfigReaderInterface $configReader,
        CollectionFactory $postFactory,
        BlogSitemapItemInterfaceFactory $itemFactory
    ) {
        $this->postFactory = $postFactory;
        $this->itemFactory = $itemFactory;
        $this->configReader = $configReader;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        if (!$this->configReader->isEnabled($storeId)) {
            return [];
        }

        $collection = $this->postFactory->create()
            ->addStoreFilter($storeId)
            ->addActiveFilter()
            ->getItems();

        $items = array_map(function ($item) use ($storeId) {
            return $this->itemFactory->create([
                'url' => $item->getUrl(),
                'updatedAt' => $item->getUpdatedAt(),
                'images' => array_merge([$item->getFeaturedImage()], $item->getGalleryImages()),
                'priority' => $this->configReader->getPriority($storeId),
                'changeFrequency' => $this->configReader->getChangeFrequency($storeId),
            ]);
        }, $collection);

        return $items;
    }
}
