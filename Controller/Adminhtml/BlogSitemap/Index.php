<?php
/**
 *
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\BlogSitemap\Controller\Adminhtml\BlogSitemap;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action;

class Index extends \Magefan\BlogSitemap\Controller\Adminhtml\BlogSitemap implements HttpGetActionInterface
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute(): void
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Blog Site Map'));
        $this->_view->renderLayout();
    }
}
