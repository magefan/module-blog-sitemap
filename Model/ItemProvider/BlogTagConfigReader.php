<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magefan\BlogSitemap\Model\ItemProvider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class BlogTagConfigReader implements ConfigReaderInterface
{
    /**#@+
     * Xpath config settings
     */
    const XML_PATH_CHANGE_FREQUENCY = 'blogsitemap/tag/changefreq';
    const XML_PATH_PRIORITY = 'blogsitemap/tag/priority';
    const XML_PATH_IS_ENABLED = 'blogsitemap/tag/enabled';
    const XML_TAG_ROBOTS = 'mfblog/tag/robots';
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
    public function getPriority($storeId): string
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
    public function getChangeFrequency($storeId): string
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
    public function isEnabled($storeId): bool
    {
        $isEnabled = (string)$this->scopeConfig->getValue(
            self::XML_PATH_IS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $tagRobots = $this->scopeConfig->getValue(
            self::XML_TAG_ROBOTS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $tagRobots = $tagRobots == 'INDEX,FOLLOW' || $tagRobots == 'NOINDEX,FOLLOW';

        return $isEnabled && $tagRobots;
    }
}
