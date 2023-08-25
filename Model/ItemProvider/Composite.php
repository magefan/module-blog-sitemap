<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\BlogSitemap\Model\ItemProvider;

class Composite implements ItemProviderInterface
{
    /**
     * Item resolvers
     *
     * @var ItemProviderInterface[]
     */
    private $itemProviders;

    /**
     * Composite constructor.
     *
     * @param ItemProviderInterface[] $itemProviders
     */
    public function __construct($itemProviders = [])
    {
        $this->itemProviders = $itemProviders;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $items = [];

        foreach ($this->itemProviders as $resolver) {
            foreach ($resolver->getItems($storeId) as $item) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
