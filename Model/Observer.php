<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\BlogSitemap\Model;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magefan\BlogSitemap\Model\EmailNotification as BlogSitemapEmail;
use Magefan\BlogSitemap\Model\ResourceModel\BlogSitemap\CollectionFactory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;

/**
 * BlogSitemap module observer
 */
class Observer
{
    /**
     * Enable/disable configuration
     */
    const XML_PATH_GENERATION_ENABLED = 'blogsitemap/generate/enabled';

    /**
     * Error email template configuration
     */
    const XML_PATH_ERROR_TEMPLATE = 'blogsitemap/generate/error_email_template';

    /**
     * Error email identity configuration
     */
    const XML_PATH_ERROR_IDENTITY = 'blogsitemap/generate/error_email_identity';

    /**
     * 'Send error emails to' configuration
     */
    const XML_PATH_ERROR_RECIPIENT = 'blogsitemap/generate/error_email';

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magefan\BlogSitemap\Model\ResourceModel\BlogSitemap\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var $emailNotification
     */
    private $emailNotification;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * Observer constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $collectionFactory
     * @param EmailNotification $emailNotification
     * @param Emulation $appEmulation
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $collectionFactory,
        BlogSitemapEmail $emailNotification,
        Emulation $appEmulation
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->collectionFactory = $collectionFactory;
        $this->emailNotification = $emailNotification;
        $this->appEmulation = $appEmulation;
    }

    /**
     * Generate blogsitemaps
     *
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function scheduledGenerateBlogSitemaps(): void
    {
        $errors = [];
        $recipient = $this->scopeConfig->getValue(
            Observer::XML_PATH_ERROR_RECIPIENT,
            ScopeInterface::SCOPE_STORE
        );
        // check if scheduled generation enabled
        if (!$this->scopeConfig->isSetFlag(
            self::XML_PATH_GENERATION_ENABLED,
            ScopeInterface::SCOPE_STORE
        )
        ) {
            return;
        }

        $collection = $this->collectionFactory->create();
        /* @var $collection \Magefan\BlogSitemap\Model\ResourceModel\BlogSitemap\Collection */
        foreach ($collection as $blogsitemap) {
            /* @var $blogsitemap \Magefan\BlogSitemap\Model\BlogSitemap */
            try {
                $this->appEmulation->startEnvironmentEmulation(
                    $blogsitemap->getStoreId(),
                    Area::AREA_FRONTEND,
                    true
                );
                $blogsitemap->generateXml();
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            } finally {
                $this->appEmulation->stopEnvironmentEmulation();
            }
        }
        if ($errors && $recipient) {
            $this->emailNotification->sendErrors($errors);
        }
    }
}
