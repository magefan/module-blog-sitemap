<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\BlogSitemap\Controller\Adminhtml;

/**
 * XML blogsitemap controller
 */
abstract class BlogSitemap extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magefan_BlogSitemap::blogsitemap';

    /**
     * Init actions
     *
     * @return $this
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magefan_BlogSitemap::blogsitemap'
        )->_addBreadcrumb(
            __('Blog'),
            __('Blog')
        )->_addBreadcrumb(
            __('XML Blog Sitemap'),
            __('XML Blog Sitemap')
        );
        return $this;
    }
}
