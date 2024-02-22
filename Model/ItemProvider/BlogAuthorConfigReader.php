<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magefan\BlogSitemap\Model\ItemProvider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class BlogAuthorConfigReader implements ConfigReaderInterface
{
    /**#@+
     * Xpath config settings
     */
    const XML_PATH_CHANGE_FREQUENCY = 'blogsitemap/author/changefreq';
    const XML_PATH_PRIORITY = 'blogsitemap/author/priority';
    const XML_PATH_IS_ENABLED = 'blogsitemap/author/enabled';
    const XML_AUTHOR_ROBOTS = 'mfblog/author/robots';
    const XML_AUTHOR_PAGE_ENABLED = 'mfblog/author/page_enabled';
    /**#@-*/

    /**
     * Scope config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * CategoryItemResolverConfigReader constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getChangeFrequency($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CHANGE_FREQUENCY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled($storeId)
    {
        $isEnabled = (string)$this->scopeConfig->getValue(
            self::XML_PATH_IS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
            );

        $authorRobots = $this->scopeConfig->getValue(
        self::XML_AUTHOR_ROBOTS,
             ScopeInterface::SCOPE_STORE,
             $storeId
            );
        $authorRobots = $authorRobots == 'INDEX,FOLLOW' || $authorRobots == 'NOINDEX,FOLLOW';

        $authorPage = $this->scopeConfig->getValue(
            self::XML_AUTHOR_PAGE_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $isEnabled && $authorPage && $authorRobots;
    }
}
