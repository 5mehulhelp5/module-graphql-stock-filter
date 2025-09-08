# Mage2AU GraphQL Stock Filter

A Magento 2 plugin that adds support for stock status filtering in GraphQL product queries, enabling efficient filtering of products by their inventory status.

## Features

- **GraphQL Stock Filtering**: Filter products by stock status in GraphQL queries
- **Multiple Value Formats**: Supports both string (`IN_STOCK`/`OUT_OF_STOCK`) and numeric (`1`/`0`) values
- **Universal Compatibility**: Works with both traditional single-stock and Multi-Source Inventory (MSI) setups
- **Performance Focused**: Efficient database joins to minimize query overhead
- **Error Handling**: Robust error handling with logging for debugging
- **Magento Standards**: Built following Magento 2 coding standards and best practices

## Installation

### Via Composer (Recommended)

```bash
composer require mage2au/module-graphql-stock-filter
php bin/magento module:enable Mage2AU_GraphQLStockFilter
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

### Manual Installation

1. Download or clone this repository
2. Copy files to `app/code/Mage2AU/GraphQLStockFilter/`
3. Enable the module:

```bash
php bin/magento module:enable Mage2AU_GraphQLStockFilter
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

## Usage

### Basic GraphQL Query

Filter products to show only in-stock items:

```graphql
query {
  products(filter: { stock_status: { eq: "IN_STOCK" } }) {
    items {
      sku
      name
      stock_status
      price_range {
        minimum_price {
          regular_price {
            value
            currency
          }
        }
      }
    }
    total_count
  }
}
```

### Advanced Filtering

Combine stock status with other filters:

```graphql
query {
  products(
    filter: { 
      sku: { match: "24-MB" }
      stock_status: { eq: "IN_STOCK" }
      price: { from: "10", to: "100" }
    }
    pageSize: 20
    currentPage: 1
  ) {
    items {
      sku
      name
      stock_status
    }
    total_count
  }
}
```

### Supported Filter Values

| Value | Description | Example |
|-------|-------------|---------|
| `"IN_STOCK"` | Products that are in stock | `stock_status: { eq: "IN_STOCK" }` |
| `"OUT_OF_STOCK"` | Products that are out of stock | `stock_status: { eq: "OUT_OF_STOCK" }` |
| `"1"` | Numeric: In stock | `stock_status: { eq: "1" }` |
| `"0"` | Numeric: Out of stock | `stock_status: { eq: "0" }` |

### Example Response

```json
{
  "data": {
    "products": {
      "items": [
        {
          "sku": "24-MB01",
          "name": "Joust Duffle Bag",
          "stock_status": "IN_STOCK"
        }
      ],
      "total_count": 1
    }
  }
}
```

### Manual Testing

You can test the module using any GraphQL client:

```bash
curl -X POST "https://your-magento-site.com/graphql" \
  -H "Content-Type: application/json" \
  -d '{
    "query": "query { products(filter: { stock_status: { eq: \"IN_STOCK\" } }) { items { sku name stock_status } } }"
  }'
```