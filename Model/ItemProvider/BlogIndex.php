<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\BlogSitemap\Model\ItemProvider;

use Magento\Store\Model\ScopeInterface;
use Magefan\BlogSitemap\Model\BlogSitemapItemInterfaceFactory;
use Magefan\Blog\Model\Url;
use Magefan\Blog\Model\Config;

/**
 * Class for adding Store Url in blogsitemap
 */
class BlogIndex implements ItemProviderInterface
{
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
     * @var Url
     */
    private $blogUrl;

    /**
     * BlogUrlBlogSitemapItemResolver constructor.
     *
     * @param ConfigReaderInterface $configReader
     * @param BlogSitemapItemInterfaceFactory $itemFactory
     */
    public function __construct(
        ConfigReaderInterface $configReader,
        BlogSitemapItemInterfaceFactory $itemFactory,
        Url $blogUrl
    ) {
        $this->itemFactory = $itemFactory;
        $this->configReader = $configReader;
        $this->blogUrl = $blogUrl;
    }

    /**
     * @inheritdoc
     */
    public function getItems($storeId)
    {
        if (!$this->configReader->isEnabled($storeId)) {
            return [];
        }

        $url = $this->blogUrl->getBasePath();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig = $objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $advancedPermalinkEnabled =  $scopeConfig->getValue(
            Config::XML_PATH_ADVANCED_PERMALINK_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        if (!$advancedPermalinkEnabled) {
            $redirectToNoSlash = $scopeConfig->getValue(
                Config::XML_PATH_REDIRECT_TO_NO_SLASH,
                ScopeInterface::SCOPE_STORE
            );

            if (!$redirectToNoSlash) {
                $url = trim($url, '/') . '/';
            }
        }


        $items[] = $this->itemFactory->create([
            'url' => $url,
            'priority' => $this->configReader->getPriority($storeId),
            'changeFrequency' => $this->configReader->getChangeFrequency($storeId),
        ]);

        return $items;
    }
}
