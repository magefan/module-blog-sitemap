<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\BlogSitemap\Block\Adminhtml;

/**
 * Adminhtml blog (google) sitemaps block
 *
 * @api
 * @since 2.0.0
 */
class BlogSitemap extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_blogsitemap';
        $this->_blockGroup = 'Magefan_BlogSitemap';
        $this->_headerText = __('XML Blog Sitemap');
        $this->_addButtonLabel = __('Add Blog Sitemap');
        parent::_construct();
    }
}
