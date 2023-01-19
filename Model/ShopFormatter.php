<?php
/**
 * ShopFormatter
 *
 * @copyright Copyright © 2023 Ushop Unilever. All rights reserved.
 * @author    ahmed.allam@unilever.com
 */

namespace Chalhoub\ShopfinderGraphQl\Model;


use Chalhoub\Shopfinder\Api\Data\ShopInterface;
use Magento\Framework\GraphQl\Query\Uid;

class ShopFormatter
{
    /**
     * @var Uid
     */
    private $idEncoder;

    /**
     * @param Uid $idEncoder
     */
    public function __construct(Uid $idEncoder)
    {
        $this->idEncoder = $idEncoder;
    }

    /**
     *
     * @param ShopInterface $shop
     * @return array
     */
    public function format(ShopInterface $shop): array
    {
        return [
            'uid' => $this->idEncoder->encode($shop->getShopId()),
            'shop_id' => $shop->getShopId(),
            'name' => $shop->getName(),
            'identifier' => $shop->getIdentifier(),
            'image' => '/shopfinder/images/' . $shop->getImage(),
            'country_id'=> $shop->getCountryId(),
            'latitude' => $shop->getLatitude(),
            'longitude' => $shop->getLongitude(),
        ];
    }
}
