<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\BlogSitemap\Controller\Adminhtml\BlogSitemap;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Validator\StringLength;
use Magento\MediaStorage\Model\File\Validator\AvailablePath;
use Magefan\BlogSitemap\Controller\Adminhtml\BlogSitemap;
use Magefan\BlogSitemap\Model\BlogSitemapConfigReaderInterface;
use Magefan\BlogSitemap\Model\BlogSitemapFactory;

/**
 * Save blogsitemap controller.
 */
class Save extends BlogSitemap implements HttpPostActionInterface
{
    /**
     * Maximum length of blogsitemap filename
     */
    const MAX_FILENAME_LENGTH = 32;

    /**
     * @var StringLength
     */
    private $stringValidator;

    /**
     * @var AvailablePath
     */
    private $pathValidator;

    /**
     * @var BlogSitemapConfigReaderInterface
     */
    private $blogSitemapConfigReader;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var BlogSitemapFactory
     */
    private $blogsitemapFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param StringLength $stringValidator
     * @param AvailablePath $pathValidator
     * @param BlogSitemapConfigReaderInterface $blogSitemapConfigReader
     * @param Filesystem $filesystem
     * @param BlogSitemapFactory $blogsitemapFactory
     */
    public function __construct(
        Context $context,
        StringLength $stringValidator,
        AvailablePath $pathValidator,
        BlogSitemapConfigReaderInterface $blogSitemapConfigReader,
        Filesystem $filesystem,
        BlogSitemapFactory $blogsitemapFactory
    ) {
        parent::__construct($context);
        $this->stringValidator = $stringValidator;
        $this->pathValidator = $pathValidator;
        $this->blogSitemapConfigReader = $blogSitemapConfigReader;
        $this->filesystem = $filesystem;
        $this->blogsitemapFactory = $blogsitemapFactory;
    }

    /**
     * Validate path for generation
     *
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    protected function validatePath(array $data): bool
    {
        if (!empty($data['blogsitemap_filename']) && !empty($data['blogsitemap_path'])) {
            $data['blogsitemap_path'] = '/' . ltrim($data['blogsitemap_path'], '/');
            $path = rtrim($data['blogsitemap_path'], '\\/') . '/' . $data['blogsitemap_filename'];
            $this->pathValidator->setPaths($this->blogSitemapConfigReader->getValidPaths());
            if (!$this->pathValidator->isValid($path)) {
                foreach ($this->pathValidator->getMessages() as $message) {
                    $this->messageManager->addErrorMessage($message);
                }
                // save data in session
                $this->_session->setFormData($data);
                // redirect to edit form
                return false;
            }

            $filename = rtrim($data['blogsitemap_filename']);
            $this->stringValidator->setMax(self::MAX_FILENAME_LENGTH);
            if (!$this->stringValidator->isValid($filename)) {
                foreach ($this->stringValidator->getMessages() as $message) {
                    $this->messageManager->addErrorMessage($message);
                }
                // save data in session
                $this->_session->setFormData($data);
                // redirect to edit form
                return false;
            }
        }
        return true;
    }

    /**
     * Clear blogsitemap
     *
     * @param \Magefan\BlogSitemap\Model\BlogSitemap $model
     *
     * @return void
     */
    protected function clearSiteMap(\Magefan\BlogSitemap\Model\BlogSitemap $model)
    {
        /** @var Filesystem $directory */
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);

        if ($this->getRequest()->getParam('blogsitemap_id')) {
            $model->load($this->getRequest()->getParam('blogsitemap_id'));
            $fileName = $model->getBlogsitemapFilename();

            $path = $model->getBlogsitemapPath() . '/' . $fileName;
            if ($fileName && $directory->isFile($path)) {
                $directory->delete($path);
            }
        }
    }

    /**
     * Save data
     *
     * @param array $data
     * @return string|bool
     */
    protected function saveData($data)
    {
        // init model and set data
        /** @var \Magefan\BlogSitemap\Model\BlogSitemap $model */
        $model = $this->blogsitemapFactory->create();
        $this->clearSiteMap($model);
        $model->setData($data);

        // try to save it
        try {
            // save the data
            $model->save();
            // display success message
            $this->messageManager->addSuccessMessage(__('You saved the blogsitemap.'));
            // clear previously saved data from session
            $this->_session->setFormData(false);
            return $model->getId();
        } catch (\Exception $e) {
            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());
            // save data in session
            $this->_session->setFormData($data);
        }
        return false;
    }

    /**
     * Get result after saving data
     *
     * @param string|bool $id
     * @return ResultInterface
     */
    protected function getResult($id)
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(Controller\ResultFactory::TYPE_REDIRECT);

        if ($id) {
            // check if 'Save and Continue'
            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath('adminhtml/*/edit', ['blogsitemap_id' => $id]);
                return $resultRedirect;
            }
            // go to grid or forward to generate action
            if ($this->getRequest()->getParam('generate')) {
                $this->getRequest()->setParam('blogsitemap_id', $id);
                return $this->resultFactory->create(Controller\ResultFactory::TYPE_FORWARD)
                    ->forward('generate');
            }
            $resultRedirect->setPath('adminhtml/*/');
            return $resultRedirect;
        }
        $resultRedirect->setPath(
            'adminhtml/*/edit',
            ['blogsitemap_id' => $this->getRequest()->getParam('blogsitemap_id')]
        );

        return $resultRedirect;
    }

    /**
     * Save action
     *
     * @return Redirect
     */
    public function execute()
    {
        // check if data sent
        $data = $this->getRequest()->getPostValue();
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(Controller\ResultFactory::TYPE_REDIRECT);
        if ($data) {
            if (!$this->validatePath($data)) {
                $resultRedirect->setPath(
                    'adminhtml/*/edit',
                    ['blogsitemap_id' => $this->getRequest()->getParam('blogsitemap_id')]
                );
                return $resultRedirect;
            }
            return $this->getResult($this->saveData($data));
        }
        $resultRedirect->setPath('adminhtml/*/');
        return $resultRedirect;
    }
}
