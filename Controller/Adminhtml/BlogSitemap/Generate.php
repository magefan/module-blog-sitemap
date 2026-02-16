<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\BlogSitemap\Controller\Adminhtml\BlogSitemap;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Area;
use Magefan\BlogSitemap\Controller\Adminhtml\BlogSitemap;
use Magento\Store\Model\App\Emulation;

/**
 * Generate blogsitemap file
 */
class Generate extends BlogSitemap implements HttpGetActionInterface
{
    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @param Action\Context $context
     * @param Emulation $appEmulation
     */
    public function __construct(
        Action\Context $context,
        Emulation $appEmulation
    ) {
        parent::__construct($context);
        $this->appEmulation = $appEmulation;
    }

    /**
     * Generate blogsitemap
     *
     * @return void
     */
    public function execute(): void
    {
        // init and load blogsitemap model
        $id = $this->getRequest()->getParam('blogsitemap_id');
        $blogsitemap = $this->_objectManager->create(\Magefan\BlogSitemap\Model\BlogSitemap::class);
        /* @var $blogsitemap \Magefan\BlogSitemap\Model\BlogSitemap */
        $blogsitemap->load($id);
        // if blogsitemap record exists
        if ($blogsitemap->getId()) {
            try {
                $this->appEmulation->startEnvironmentEmulation(
                    $blogsitemap->getStoreId(),
                    Area::AREA_FRONTEND,
                    true
                );
                $blogsitemap->generateXml();
                $this->appEmulation->stopEnvironmentEmulation();
                $this->messageManager->addSuccessMessage(
                    __('The blogsitemap "%1" has been generated.', $blogsitemap->getBlogsitemapFilename())
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t generate the blogsitemap right now.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find a blogsitemap to generate.'));
        }

        // go to grid
        $this->_redirect('adminhtml/*/');
    }
}
