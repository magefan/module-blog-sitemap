<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\BlogSitemap\Controller\Adminhtml\BlogSitemap;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magefan\BlogSitemap\Controller\Adminhtml\BlogSitemap;
use Magefan\BlogSitemap\Model\BlogSitemapFactory;

/**
 * Controller class Delete. Represents adminhtml request flow for a blogsitemap deletion
 */
class Delete extends BlogSitemap implements HttpPostActionInterface
{
    /**
     * @var BlogSitemapFactory
     */
    private $blogsitemapFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Context $context
     * @param BlogSitemapFactory $blogsitemapFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        BlogSitemapFactory $blogsitemapFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->blogsitemapFactory = $blogsitemapFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function execute()
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('blogsitemap_id');
        if ($id) {
            try {
                // init model and delete
                /** @var \Magefan\BlogSitemap\Model\BlogSitemap $blogsitemap */
                $blogsitemap = $this->blogsitemapFactory->create();
                $blogsitemap->load($id);
                // delete file
                $blogsitemapPath = $blogsitemap->getBlogsitemapPath();
                if ($blogsitemapPath && $blogsitemapPath[0] === DIRECTORY_SEPARATOR) {
                    $blogsitemapPath = mb_substr($blogsitemapPath, 1);
                }
                $blogsitemapFilename = $blogsitemap->getBlogsitemapFilename();
                $path = $directory->getRelativePath(
                    $blogsitemapPath .$blogsitemapFilename
                );
                if ($blogsitemap->getBlogsitemapFilename() && $directory->isFile($path)) {
                    $directory->delete($path);
                }
                $blogsitemap->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the blogsitemap.'));
                // go to grid
                $this->_redirect('adminhtml/*/');
                return;
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                $this->_redirect('adminhtml/*/edit', ['blogsitemap_id' => $id]);
                return;
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a blogsitemap to delete.'));
        // go to grid
        $this->_redirect('adminhtml/*/');
    }
}
