
# Shopfinder GraphQL module

this module should expose graphql apis for the Chalhoub_Shopfinder module.

## Installation

Install extension with composer

```bash
  composer require eggate/chalhoub-shopfinder-graph-ql
```
Run magento installion commands

```bash
  bin/magento setup:install
  bin/magento setup:di:compile
  bin/magento setup:static-content:deploy --area adminhtml
  bin/magento cache:flush
``` 

## Features

- We should be able to use any graphql playground.
- We should be able to use a query to fetch all the shops.
- We should be able to use a mutation to update any store information.
- We should not be able to delete stores from the API - Provide aproper error handling for this case. 
- We should be able to use a query to fetch information about a single shop based on the identifier.
- (Optional) We should be able to fetch the stores near me, based on my current location.



## GraphQL API Reference

#### Get all shops\filtered shops 

```graphql
  query {
    Shops(
        currentPage: ${currentPage}
        pageSize: ${pageSize}
        input: {
            identifier: "filter by identifier"
            name : "filter by name"
            shop_ids: "filter by ids"
        }
    ){
        total_count
        items{
            shop_id
            name
            identifier
            image
            country_id
            latitude
            longitude
        }
    }
}
```
| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `currentPage`      | `int` | **Optional**. current page |
| `pageSize`      | `string` | **Optional**. page size |
| `input`      | `ShopFilterInput` | **Otional**. shop filter input |

ShopFilterInput schema

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `identifier`      | `string` | **Optional**. identifier |
| `name`      | `string` | **Optional**. name|
| `shop_ids`      | `[int]` | **Otional**. array of shop ids |

#### Get shop by identifier

```graphql
  query {
    Shop(
      identifier: ${identifier}
    ){
        shop_id
        name
        identifier
        image
        country_id
        latitude
        longitude
    }
}
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `identifier`      | `string` | **Required**. shop idnetifier |

#### Get nearest shops based on location (Haversine equation) distance in Km

```graphql
  query {
    nearestShops(
        currentPage: 1
        pageSize: 20
        coords: {
            lat: "location latitude"
            long : "location longitude"
            radius: "search radius"
        }
    ){
        total_count
        items{
            shop_id
            name
            identifier
            image
            country_id
            latitude
            longitude
        }
    }
}
```
#### Save new or update existing shop

```graphql
mutation{
    saveShop(
        shop:{
            shop_id : ${shop_id}
            name: ${name}
            identifier: ${identifier}
            image: ${image}
            country_id: ${country_id}
            latitude: ${latitude}
            longitude: ${longitude}
        }
    ){
        shop {
            shop_id
            name
            identifier
            image
            country_id
            latitude
            longitude
        }
    }
}
```
| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `shop_id`      | `int` | **Otional**.  shop id if you need to update shop|
| `name`      | `string` | **Required**. name|
| `identifier`      | `string` | **Required**. identifier |
| `image`      | `string` | **Required**. Shop image url ** I haven't handled downloading image from remote URL ** |
| `country_id`      | `string` | **Required**. ISO2 code for country |
| `latitude`      | `string` | **Optional**. Shop latitude |
| `longitude`      | `string` | **Optional**. Shop Longitude |

#### Delete shop by id or identifier

```graphql
mutation{
    deleteShop(
        filter:{
            shop_id : ${shop_id}
            identifier: ${identifier}
        }
    ){
        message
    }
}
```

Only provide 1 filter parameter
| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `shop_id`      | `int` | **RequiredIf**.  delete shop by shop id |
| `identifier`      | `string` | **RequiredIf**. delete shop by identifier  |
