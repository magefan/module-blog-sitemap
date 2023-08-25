<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\BlogSitemap\Model\ItemProvider;

use Magefan\BlogSitemap\Model\BlogSitemapItemInterface;

/**
 * BlogSitemap item provider interface
 *
 * @api
 * @since 2.0.0
 */
interface ItemProviderInterface
{
    /**
     * Get blogsitemap items
     *
     * @param int $storeId
     * @return BlogSitemapItemInterface[]
     * @since 2.0.0
     */
    public function getItems($storeId);
}
