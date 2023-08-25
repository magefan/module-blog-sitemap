<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\BlogSitemap\Model\ItemProvider;

/**
 * Item resolver config reader interface
 *
 * @api
 * @since 2.0.0
 */
interface ConfigReaderInterface
{
    /**
     * Get priority
     *
     * @param int $storeId
     * @return string
     * @since 2.0.0
     */
    public function getPriority($storeId);

    /**
     * Get change frequency
     *
     * @param int $storeId
     * @return string
     * @since 2.0.0
     */
    public function getChangeFrequency($storeId);
}
