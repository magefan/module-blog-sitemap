<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\BlogSitemap\Controller\Adminhtml\BlogSitemap;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Block\Template;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Registry;
use Magefan\BlogSitemap\Controller\Adminhtml\BlogSitemap;

/**
 * Controller class Edit. Responsible for rendering of a blogsitemap edit page
 */
class Edit extends BlogSitemap implements HttpGetActionInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(Context $context, Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Edit blogsitemap
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('blogsitemap_id');
        $model = $this->_objectManager->create(\Magefan\BlogSitemap\Model\BlogSitemap::class);

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This blogsitemap no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get(Session::class)->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('blogsitemap_blogsitemap', $model);

        // 5. Build edit form
        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit Blog Sitemap') : __('New Blog Sitemap'),
            $id ? __('Edit Blog Sitemap') : __('New Blog Sitemap')
        )->_addContent(
            $this->_view->getLayout()->createBlock(\Magefan\BlogSitemap\Block\Adminhtml\Edit::class)
        )->_addJs(
            $this->_view->getLayout()->createBlock(Template::class)->setTemplate('Magefan_BlogSitemap::js.phtml')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Blog Site Map'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? $model->getBlogsitemapFilename() : __('New Blog Site Map')
        );
        $this->_view->renderLayout();
    }
}
