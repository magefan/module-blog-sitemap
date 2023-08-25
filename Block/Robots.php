<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\BlogSitemap\Block;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Robots\Model\Config\Value;
use Magefan\BlogSitemap\Model\ResourceModel\BlogSitemap\CollectionFactory;
use Magefan\BlogSitemap\Model\BlogSitemap;
use Magefan\BlogSitemap\Model\BlogSitemapConfigReader;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreResolver;

/**
 * Prepares blogsitemap links to add to the robots.txt file
 *
 * @api
 * @since 2.0.0
 */
class Robots extends AbstractBlock implements IdentityInterface
{
    /**
     * @var CollectionFactory
     */
    private $blogsitemapCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var BlogSitemapConfigReader
     */
    private $blogsitemapConfigReader;

    /**
     * @param Context $context
     * @param StoreResolver $storeResolver
     * @param CollectionFactory $blogsitemapCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param array $data
     * @param BlogSitemapConfigReader|null $blogsitemapConfigReader
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context $context,
        StoreResolver $storeResolver,
        CollectionFactory $blogsitemapCollectionFactory,
        StoreManagerInterface $storeManager,
        array $data = [],
        ?BlogSitemapConfigReader $blogsitemapConfigReader = null
    ) {
        $this->blogsitemapCollectionFactory = $blogsitemapCollectionFactory;
        $this->storeManager = $storeManager;
        $this->blogsitemapConfigReader = $blogsitemapConfigReader
            ?: ObjectManager::getInstance()->get(BlogSitemapConfigReader::class);

        parent::__construct($context, $data);
    }

    /**
     * Prepare blogsitemap links to add to the robots.txt file
     *
     * Collects blogsitemap links for all stores of given website.
     * Detects if blogsitemap file information is required to be added to robots.txt
     * and adds links for this blogsitemap files into result data.
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        $website = $this->storeManager->getWebsite();

        $storeIds = [];
        foreach ($website->getStoreIds() as $storeId) {
            if ((bool) $this->blogsitemapConfigReader->getEnableSubmissionRobots($storeId)) {
                $storeIds[] = (int) $storeId;
            }
        }

        $links = $storeIds ? $this->getBlogsitemapLinks($storeIds) : [];

        return $links ? implode(PHP_EOL, $links) . PHP_EOL : '';
    }

    /**
     * Retrieve blogsitemap links for given store
     *
     * Gets the names of blogsitemap files that linked with given store,
     * and adds links for this blogsitemap files into result array.
     *
     * @param int[] $storeIds
     * @return array
     * @since 2.0.0
     */
    protected function getBlogsitemapLinks(array $storeIds)
    {
        $collection = $this->blogsitemapCollectionFactory->create();
        $collection->addStoreFilter($storeIds);

        $blogsitemapLinks = [];
        /**
         * @var BlogSitemap $blogsitemap
         */
        foreach ($collection as $blogsitemap) {
            $blogsitemapUrl = $blogsitemap->getBlogsitemapUrl($blogsitemap->getBlogsitemapPath(), $blogsitemap->getBlogsitemapFilename());
            $blogsitemapLinks[$blogsitemapUrl] = 'BlogSitemap: ' . $blogsitemapUrl;
        }

        return $blogsitemapLinks;
    }

    /**
     * Get unique page cache identities
     *
     * @return array
     * @since 2.0.0
     */
    public function getIdentities()
    {
        return [
            Value::CACHE_TAG . '_' . $this->storeManager->getDefaultStoreView()->getId(),
        ];
    }
}
