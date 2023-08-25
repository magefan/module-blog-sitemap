<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\BlogSitemap\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class BlogSitemapConfigReader implements BlogSitemapConfigReaderInterface
{
    /**
     * Config path to blogsitemap valid paths
     */
    const XML_PATH_SITEMAP_VALID_PATHS = 'blogsitemap/file/valid_paths';

    /**
     * Config path to valid file paths
     */
    const XML_PATH_PUBLIC_FILES_VALID_PATHS = 'general/file/public_files_valid_paths';

    /**#@+
     * Limits xpath config settings
     */
    const XML_PATH_MAX_LINES = 'blogsitemap/limit/max_lines';
    const XML_PATH_MAX_FILE_SIZE = 'blogsitemap/limit/max_file_size';
    /**#@-*/

    /**
     * Search Engine Submission Settings
     */
    const XML_PATH_SUBMISSION_ROBOTS = 'blogsitemap/search_engines/submission_robots';

    /**
     * Include post image setting
     */
    const XML_PATH_POST_IMAGES_INCLUDE = 'blogsitemap/post/image_include';

    /**
     * Scope config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * BlogSitemap Config Reader constructor.
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
    public function getEnableSubmissionRobots($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SUBMISSION_ROBOTS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMaximumFileSize($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAX_FILE_SIZE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMaximumLinesNumber($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAX_LINES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPostImageIncludePolicy($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_POST_IMAGES_INCLUDE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getValidPaths()
    {
        return array_merge(
            $this->scopeConfig->getValue(self::XML_PATH_SITEMAP_VALID_PATHS, ScopeInterface::SCOPE_STORE),
            $this->scopeConfig->getValue(self::XML_PATH_PUBLIC_FILES_VALID_PATHS, ScopeInterface::SCOPE_STORE)
        );
    }
}
