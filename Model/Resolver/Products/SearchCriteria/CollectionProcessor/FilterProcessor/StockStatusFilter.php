<?php
/**
 * Mage2AU GraphQL Stock Filter Module
 * StockStatusFilter - Filters products by stock status (IN_STOCK or OUT_OF_STOCK)
 * 
 * @category    Mage2AU
 * @package     Mage2AU_GraphQLStockFilter
 * @author      Mage2AU
 * @copyright   Copyright (c) 2025 Mage2AU
 * @license     MIT License
 * @version     1.0.0
 */

declare(strict_types=1);

namespace Mage2AU\GraphQLStockFilter\Model\Resolver\Products\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\CatalogInventory\Model\Stock;
use Psr\Log\LoggerInterface;

class StockStatusFilter implements CustomFilterInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Apply stock_status Filter to Product Collection
     *
     * This method filters products based on their stock status using Magento's
     * official stock filtering approach:
     * - "IN_STOCK" or "1" = Show only in-stock products
     * - "OUT_OF_STOCK" or "0" = Show only out-of-stock products
     *
     * @param Filter $filter The filter containing field, condition, and value
     * @param AbstractDb $collection The product collection to filter
     * @return bool Whether the filter was applied successfully
     */
    public function apply(Filter $filter, AbstractDb $collection): bool
    {

        try {
            $value = $filter->getValue();
            
            // Normalize the filter value to integer (0 or 1)
            $stockStatus = $this->normalizeStockStatus($value);

            // For single-stock sites (non-MSI), website_id is always 0
            $websiteId = 0;
            
            // Build the join condition for stock status table
            $connection = $collection->getConnection();
            $joinCondition = $connection->quoteInto(
                'e.entity_id = stock_status_filter.product_id AND stock_status_filter.website_id = ?',
                $websiteId
            );
            $joinCondition .= $connection->quoteInto(
                ' AND stock_status_filter.stock_id = ?',
                Stock::DEFAULT_STOCK_ID
            );


            // Check if join already exists to prevent duplicates
            $fromPart = $collection->getSelect()->getPart('from');
            if (!isset($fromPart['stock_status_filter'])) {
                // Join the stock status table (similar to how Magento does it)
                $collection->getSelect()->join(
                    ['stock_status_filter' => 'cataloginventory_stock_status'],
                    $joinCondition,
                    []
                );
            }

            // Apply the stock status filter
            $collection->getSelect()->where('stock_status_filter.stock_status = ?', $stockStatus);

            return true;
        } catch (\Throwable $e) {
            $this->logger->error('StockStatusFilter::apply() - Error applying stock status filter: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Normalize stock status value to integer
     * 
     * Converts various input formats to the database integer value:
     * - "IN_STOCK" or "1" → 1 (in stock)
     * - "OUT_OF_STOCK" or "0" → 0 (out of stock)
     * - Invalid values default to 0 (out of stock)
     *
     * @param mixed $value The filter value from GraphQL query
     * @return int 0 for out-of-stock, 1 for in-stock
     */
    private function normalizeStockStatus($value): int
    {
        // Handle string values
        if (is_string($value)) {
            $upperValue = strtoupper(trim($value));
            
            if ($upperValue === 'IN_STOCK' || $upperValue === '1') {
                return 1; // STATUS_IN_STOCK
            } elseif ($upperValue === 'OUT_OF_STOCK' || $upperValue === '0') {
                return 0; // STATUS_OUT_OF_STOCK
            }
        }

        // Handle numeric values
        if (is_numeric($value)) {
            $intValue = (int) $value;
            return ($intValue === 1) ? 1 : 0; // 1 = IN_STOCK, 0 = OUT_OF_STOCK
        }

        // Default to out-of-stock for invalid values
        return 0; // STATUS_OUT_OF_STOCK
    }
}