<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\BlogSitemap\Model;

/**
 * BlogSitemap config reader interface
 *
 * @api
 * @since 2.0.0
 */
interface BlogSitemapConfigReaderInterface
{
    /**
     * Get enable Submission to Robots.txt
     *
     * @param int $storeId
     * @return int
     * @since 2.0.0
     */
    public function getEnableSubmissionRobots($storeId);

    /**
     * Get maximum blogsitemap.xml file size in bytes
     *
     * @param int $storeId
     * @return int
     * @since 2.0.0
     */
    public function getMaximumFileSize($storeId);

    /**
     * Get maximum blogsitemap.xml URLs number
     *
     * @param int $storeId
     * @return int
     * @since 2.0.0
     */
    public function getMaximumLinesNumber($storeId);

    /**
     * Get post image include policy
     *
     * @param int $storeId
     * @return string
     * @since 2.0.0
     */
    public function getPostImageIncludePolicy($storeId);

    /**
     * Get list valid paths for generate a blogsitemap XML file
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getValidPaths();
}
