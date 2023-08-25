<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\BlogSitemap\Model\ResourceModel;

/**
 * BlogSitemap resource model
 *
 * @api
 * @since 2.0.0
 */
class BlogSitemap extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('blogsitemap', 'blogsitemap_id');
    }
}
