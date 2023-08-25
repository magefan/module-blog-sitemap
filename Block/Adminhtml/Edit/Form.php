<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\BlogSitemap\Block\Adminhtml\Edit;

/**
 * BlogSitemap edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('blogsitemap_form');
        $this->setTitle(__('Blog Sitemap Information'));
    }

    /**
     * Configure form for blogsitemap.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('blogsitemap_blogsitemap');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('add_blogsitemap_form', ['legend' => __('Blog Sitemap')]);

        if ($model->getId()) {
            $fieldset->addField('blogsitemap_id', 'hidden', ['name' => 'blogsitemap_id']);
        }

        $fieldset->addField(
            'blogsitemap_filename',
            'text',
            [
                'label' => __('Filename'),
                'name' => 'blogsitemap_filename',
                'required' => true,
                'note' => __('example: blogsitemap.xml'),
                'value' => $model->getBlogsitemapFilename(),
                'class' => 'validate-length maximum-length-32'
            ]
        );

        $fieldset->addField(
            'blogsitemap_path',
            'text',
            [
                'label' => __('Path'),
                'name' => 'blogsitemap_path',
                'required' => true,
                'note' => __('example: "/media/blogsitemap/" for base path (path must be writeable)'),
                'value' => $model->getBlogsitemapPath()
            ]
        );

        if (!$this->_storeManager->hasSingleStore()) {
            $field = $fieldset->addField(
                'store_id',
                'select',
                [
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'name' => 'store_id',
                    'required' => true,
                    'value' => $model->getStoreId(),
                    'values' => $this->_systemStore->getStoreValuesForForm()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'store_id', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
