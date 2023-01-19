<?php
/**
 * NearestShops
 *
 * @copyright Copyright © 2023 Ushop Unilever. All rights reserved.
 * @author    ahmed.allam@unilever.com
 */

namespace Chalhoub\ShopfinderGraphQl\Model\Resolver;


use Chalhoub\Shopfinder\Model\ResourceModel\Shop;
use Chalhoub\Shopfinder\Model\ResourceModel\Shop\CollectionFactory;
use Chalhoub\ShopfinderGraphQl\Model\ShopFormatter;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\ResourceConnection;
class NearestShops  implements ResolverInterface
{
    protected $collectionFactory;
    protected $formatter;
    protected $resourceConnection;


    public function __construct(
        CollectionFactory $collectionFactory,
        ShopFormatter $formatter,
        ResourceConnection $resourceConnection
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->formatter = $formatter;
        $this->resourceConnection = $resourceConnection;
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
        if(empty($args['coords']['long']) || empty($args['coords']['lat']))
            throw new GraphQlInputException(__("you must provide correct coordinates"));

        $connection = $this->resourceConnection->getConnection();
        //Haversine equation
        $sql = "SELECT shop_id, ( 6371  * acos( cos( radians(37) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(:lng) ) + sin( radians(:lat) ) * sin( radians( latitude ) ) ) ) AS distance FROM " . Shop::TABLE_NAME . " HAVING distance < :radius ORDER BY distance";
        $result = $connection->fetchAll($sql, [
            ":lng" => $args['coords']['long'],
            ":lat" => $args['coords']['lat'],
            ":radius" => !empty($args['coords']['radius']) ? $args['coords']['radius'] : 25
        ]);
        $data = [];
        foreach ($result as $dbItem) {
            $data[$dbItem['shop_id']] = $dbItem['distance'];
        }
        $shops = [];
        $shopsCollection = $this->collectionFactory->create();
        $shopsCollection->addFieldToFilter("shop_id", ['in' => array_keys($data)]);
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

