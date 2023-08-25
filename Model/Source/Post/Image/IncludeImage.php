<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

/**
 * Image include policy into blogsitemap file
 *
 */
namespace Magefan\BlogSitemap\Model\Source\Post\Image;

/**
 * @api
 * @since 2.0.0
 */
class IncludeImage implements \Magento\Framework\Option\ArrayInterface
{
    /**#@+
     * Add Images into BlogSitemap possible values
     */
    const INCLUDE_NONE = 'none';

    const INCLUDE_BASE = 'base';

    const INCLUDE_ALL = 'all';

    /**#@-*/

    /**
     * Retrieve options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::INCLUDE_NONE => __('None'),
            self::INCLUDE_BASE => __('Base Only'),
            self::INCLUDE_ALL => __('All')
        ];
    }
}
