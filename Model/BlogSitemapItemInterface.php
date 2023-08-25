<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\BlogSitemap\Model;

/**
 * Representation of blogsitemap item
 *
 * @api
 * @since 2.0.0
 */
interface BlogSitemapItemInterface
{

    /**
     * Get url
     *
     * @return string
     * @since 2.0.0
     */
    public function getUrl();

    /**
     * Get priority
     *
     * @return string
     * @since 2.0.0
     */
    public function getPriority();

    /**
     * Get change frequency
     *
     * @return string
     * @since 2.0.0
     */
    public function getChangeFrequency();

    /**
     * Get images
     *
     * @return array|null
     * @since 2.0.0
     */
    public function getImages();

    /**
     * Get last update date
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getUpdatedAt();
}
