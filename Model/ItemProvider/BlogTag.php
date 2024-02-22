<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magefan\BlogSitemap\Model\ItemProvider;

use Magefan\Blog\Model\ResourceModel\Tag\CollectionFactory;
use Magefan\BlogSitemap\Model\BlogSitemapItemInterfaceFactory;

class BlogTag implements ItemProviderInterface
{
    /**
     * Tag factory
     *
     * @var CollectionFactory
     */
    private $tagFactory;

    /**
     * Sitemap item factory
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
     * CategorySitemapItemResolver constructor.
     *
     * @param ConfigReaderInterface $configReader
     * @param CollectionFactory $tagFactory
     * @param BlogSitemapItemInterfaceFactory $itemFactory
     */
    public function __construct(
        ConfigReaderInterface $configReader,
        CollectionFactory $tagFactory,
        BlogSitemapItemInterfaceFactory $itemFactory
    ) {
        $this->tagFactory = $tagFactory;
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

        $collection = $this->tagFactory->create()
            ->addStoreFilter($storeId)
            ->addActiveFilter()
            ->getItems();

        $items = array_map(function ($item) use ($storeId) {
            return $this->itemFactory->create([
                'url' => $item->getUrl(),
                'updatedAt' => $item->getUpdatedAt(),
                'images' => $item->getImages(),
                'priority' => $this->configReader->getPriority($storeId),
                'changeFrequency' => $this->configReader->getChangeFrequency($storeId),
            ]);
        }, $collection);

        return $items;
    }
}
