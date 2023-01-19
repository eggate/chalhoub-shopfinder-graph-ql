<?php
/**
 * ShopIdentifier
 *
 * @copyright Copyright © 2023 Ushop Unilever. All rights reserved.
 * @author    ahmed.allam@unilever.com
 */

namespace Chalhoub\ShopfinderGraphQl\Model\Resolver;


use Chalhoub\Shopfinder\Model\ShopFactory;
use Chalhoub\ShopfinderGraphQl\Model\ShopFormatter;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ShopIdentifier implements ResolverInterface
{
    protected $shopFactory;
    protected $formatter;

    public function __construct(
        ShopFactory $shopFactory,
        ShopFormatter $formatter
    ) {
        $this->shopFactory = $shopFactory;
        $this->formatter = $formatter;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $shop = $this->shopFactory->create();
        $shop->load($args['identifier'], 'identifier');

        if(!$shop->getShopId())
            throw new GraphQlInputException(__("Couldn't find shop with Identifier %1", $args['identifier']));

        return $this->formatter->format($shop);
    }
}

