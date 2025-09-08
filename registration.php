<?php
/**
 * Mage2AU GraphQL Stock Filter Module Registration
 * 
 * @category    Mage2AU
 * @package     Mage2AU_GraphQLStockFilter
 * @author      Mage2AU
 * @copyright   Copyright (c) 2025 Mage2AU
 * @license     MIT License
 * @version     1.0.0
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Mage2AU_GraphQLStockFilter',
    __DIR__
);