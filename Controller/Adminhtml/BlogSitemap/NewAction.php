<?php
/**
 *
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\BlogSitemap\Controller\Adminhtml\BlogSitemap;

class NewAction extends \Magefan\BlogSitemap\Controller\Adminhtml\BlogSitemap
{
    /**
     * Create new blogsitemap
     *
     * @return void
     */
    public function execute()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }
}
