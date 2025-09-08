<?php
/**
 * Plugin to preserve stock_status filter in ProductCollectionSearchCriteriaBuilder
 */
namespace Mage2AU\GraphQLStockFilter\Plugin;

use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch\ProductCollectionSearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Psr\Log\LoggerInterface;

class ProductCollectionSearchCriteriaBuilderPlugin
{
    private LoggerInterface $logger;
    private FilterBuilder $filterBuilder;
    private FilterGroupBuilder $filterGroupBuilder;

    public function __construct(
        LoggerInterface $logger,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->logger = $logger;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    public function afterBuild(
        ProductCollectionSearchCriteriaBuilder $subject,
        SearchCriteriaInterface $result,
        SearchCriteriaInterface $originalSearchCriteria
    ): SearchCriteriaInterface {
        // Find stock_status filters in original SearchCriteria
        $stockStatusFilters = [];
        foreach ($originalSearchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'stock_status') {
                    $stockStatusFilters[] = $filter;
                }
            }
        }

        // If we found stock_status filters, add them back to the result
        if (!empty($stockStatusFilters)) {
            $existingFilterGroups = $result->getFilterGroups();
            
            // Create a new filter group for stock_status filters
            $this->filterGroupBuilder = clone $this->filterGroupBuilder; // Reset the builder
            
            foreach ($stockStatusFilters as $stockStatusFilter) {
                $newFilter = $this->filterBuilder
                    ->setField($stockStatusFilter->getField())
                    ->setValue($stockStatusFilter->getValue())
                    ->setConditionType($stockStatusFilter->getConditionType())
                    ->create();
                
                $this->filterGroupBuilder->addFilter($newFilter);
            }
            
            $stockStatusFilterGroup = $this->filterGroupBuilder->create();
            $existingFilterGroups[] = $stockStatusFilterGroup;
            
            $result->setFilterGroups($existingFilterGroups);
        }
        
        return $result;
    }
}