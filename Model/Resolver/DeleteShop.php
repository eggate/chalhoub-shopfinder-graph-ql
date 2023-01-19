<?php
/**
 * ShopsMutation
 *
 * @copyright Copyright © 2023 Ushop Unilever. All rights reserved.
 * @author    ahmed.allam@unilever.com
 */

namespace Chalhoub\ShopfinderGraphQl\Model\Resolver;


use Chalhoub\Shopfinder\Api\Data\ShopInterfaceFactory;
use Chalhoub\Shopfinder\Api\ShopRepositoryInterface;
use Chalhoub\Shopfinder\Model\Shop;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class DeleteShop implements ResolverInterface
{
    protected $shopRepository;
    protected $shopInterfaceFactory;
    protected $shopFormatter;

    public function __construct(
        ShopRepositoryInterface $shopRepository,
        ShopInterfaceFactory    $shopInterfaceFactory
    )
    {
        $this->shopRepository = $shopRepository;
        $this->shopInterfaceFactory = $shopInterfaceFactory;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $customerId = (int)$context->getUserId() ?? null;
        if (null === $customerId || 0 === $customerId) {
            throw new GraphQlAuthorizationException(__(
                'The current user cannot perform operations on %1',
                ['shop finder']
            ));
        }
        if(empty($args['filter']['identifier']) && empty($args['filter']['shop_id']) )
            throw new GraphQlInputException(__("you must provide shop_id or identifier"));

        /** @var Shop $shop */
        $shop = $this->shopInterfaceFactory->create();
        if(isset($args['filter']['identifier']))
            $shop->load($args['filter']['identifier'], 'identifier');

        if(isset($args['filter']['shop_id']))
            $shop->load($args['filter']['shop_id'], 'shop_id');

        if(!$shop->getShopId())
            throw new GraphQlInputException(__("Cannot find shop with provided details"));
        try {
            $this->shopRepository->delete($shop);
        } catch (\Exception $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }
        return [
            'message' => __("Shop has been successfully deleted")
        ];
    }
}
