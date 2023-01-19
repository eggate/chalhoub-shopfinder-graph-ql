<?php
/**
 * Shops
 *
 * @copyright Copyright © 2023 Ushop Unilever. All rights reserved.
 * @author    ahmed.allam@unilever.com
 */

namespace Chalhoub\ShopfinderGraphQl\Model\Resolver;


use Chalhoub\Shopfinder\Model\ResourceModel\Shop\CollectionFactory;
use Chalhoub\ShopfinderGraphQl\Model\ShopFormatter;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Shops implements ResolverInterface
{
    protected $collectionFactory;
    protected $formatter;

    public function __construct(
        CollectionFactory $collectionFactory,
        ShopFormatter $formatter
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->formatter = $formatter;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {


        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        $shopsCollection = $this->collectionFactory->create();
        if (isset($args['input']['shop_ids'])) {
           $shopsCollection->addFieldToFilter('shop_id', ['in' => $args['input']['shop_ids']]);
        }
        if (isset($args['input']['name'])) {
            $shopsCollection->addFieldToFilter('name', ['like' => '%' . $args['input']['name'] . '%']);
        }
        if (isset($args['input']['identifier'])) {
            $shopsCollection->addFieldToFilter('shop_id',['like' => '%' . $args['input']['identifier'] . '%']);
        }
        if (isset($args['input']['country_id'])) {
            $shopsCollection->addFieldToFilter('country_id',['eq' =>  $args['input']['country_id'] ]);
        }

        $shops = [];
        foreach ($shopsCollection->getItems() as $shop) {
            $shops[] = $this->formatter->format($shop);
        }

        $pageSize = $shopsCollection->getPageSize();
        $totalCount = $shopsCollection->getSize();

        return [
            'items' => $shops,
            'page_info' => [
                'page_size' => $pageSize,
                'current_page' => $shopsCollection->getCurPage(),
                'total_pages' => $pageSize ? ((int)ceil($totalCount / $pageSize)) : 0,
            ],
            'total_count' => $totalCount
        ];
    }
}

