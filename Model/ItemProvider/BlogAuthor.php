<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magefan\BlogSitemap\Model\ItemProvider;

use Magefan\Blog\Api\AuthorCollectionInterfaceFactory as CollectionFactory;
use Magefan\BlogSitemap\Model\BlogSitemapItemInterfaceFactory;
use Magento\Framework\Module\Manager;

class BlogAuthor implements ItemProviderInterface
{
    /**
     * Category factory
     *
     * @var CollectionFactory
     */
    private $authorFactory;

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
     * @var Manager
     */
    private $moduleManager;

    /**
     * BlogAuthor constructor.
     *
     * @param ConfigReaderInterface $configReader
     * @param CollectionFactory $authorFactory
     * @param BlogSitemapItemInterfaceFactory $itemFactory
     * @param Manager $moduleManager
     */
    public function __construct(
        ConfigReaderInterface $configReader,
        CollectionFactory $authorFactory,
        BlogSitemapItemInterfaceFactory $itemFactory,
        Manager $moduleManager
    ) {
        $this->authorFactory = $authorFactory;
        $this->itemFactory = $itemFactory;
        $this->configReader = $configReader;
        $this->moduleManager = $moduleManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        if ($this->moduleManager->isEnabled('Magefan_BlogAuthor')) {
            $collection = $this->authorFactory->create()
                ->addStoreFilter($storeId)
                ->addActiveFilter()
                ->getItems();
        } else {
            $collection = $this->authorFactory->create()
                ->addFieldToFilter('is_active', 1)
                ->getItems();
        }

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
