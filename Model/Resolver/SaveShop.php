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
use Chalhoub\ShopfinderGraphQl\Model\ShopFormatter;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SaveShop implements ResolverInterface
{
    protected $shopRepository;
    protected $shopInterfaceFactory;
    protected $shopFormatter;

    public function __construct(
        ShopRepositoryInterface $shopRepository,
        ShopInterfaceFactory    $shopInterfaceFactory,
        ShopFormatter           $shopFormatter
    )
    {
        $this->shopRepository = $shopRepository;
        $this->shopInterfaceFactory = $shopInterfaceFactory;
        $this->shopFormatter = $shopFormatter;
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
        /** @var Shop $shop */
        $shop = $this->shopInterfaceFactory->create();
        if(!empty($args['shop']['shop_id'])){
            try{
                $shop = $this->shopRepository->getById($args['shop']['shop_id']);
            }catch (\Exception $e){
                throw new GraphQlInputException(__($e->getMessage()));
            }
        }
        $shop->addData($args['shop']);
        try {
            $this->shopRepository->save($shop);
        } catch (\Exception $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }
        return [
            'shop' => $this->shopFormatter->format($shop)
        ];
    }
}
