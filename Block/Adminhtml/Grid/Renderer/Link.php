<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\BlogSitemap\Block\Adminhtml\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magefan\BlogSitemap\Model\BlogSitemap;
use Magefan\BlogSitemap\Model\BlogSitemapFactory;

/**
 * BlogSitemap grid link column renderer
 */
class Link extends AbstractRenderer
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var BlogSitemapFactory
     */
    private $blogsitemapFactory;

    /**
     * @var DocumentRoot
     */
    private $documentRoot;

    /**
     * @param Context $context
     * @param BlogSitemapFactory $blogsitemapFactory
     * @param Filesystem $filesystem
     * @param DocumentRoot $documentRoot
     * @param array $data
     */
    public function __construct(
        Context $context,
        BlogSitemapFactory $blogsitemapFactory,
        Filesystem $filesystem,
        DocumentRoot $documentRoot,
        array $data = []
    ) {
        $this->blogsitemapFactory = $blogsitemapFactory;
        $this->filesystem = $filesystem;
        $this->documentRoot = $documentRoot;

        parent::__construct($context, $data);
    }

    /**
     * Prepare link to display in grid
     *
     * @param DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        /** @var $blogsitemap BlogSitemap */
        $blogsitemap = $this->blogsitemapFactory->create();
        $blogsitemap->setStoreId($row->getStoreId());
        $url = $this->_escaper->escapeHtml($blogsitemap->getBlogsitemapUrl($row->getBlogsitemapPath(), $row->getBlogsitemapFilename()));

        $fileName = preg_replace('/^\//', '', $row->getBlogsitemapPath() . $row->getBlogsitemapFilename());
        $documentRootPath = $this->documentRoot->getPath();
        $directory = $this->filesystem->getDirectoryRead($documentRootPath);
        if ($directory->isFile($fileName)) {
            return sprintf('<a href="%1$s">%1$s</a>', $url);
        }

        return $url;
    }
}
