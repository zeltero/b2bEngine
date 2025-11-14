<?php
/**
 * Zeltero B2B Module Registration
 * 
 * This module provides B2B e-commerce functionality including:
 * - Company registration and approval
 * - Wholesale pricing per customer group
 * - Quick order functionality
 * - B2B payment methods
 * - ERP integration
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Zeltero_B2B',
    __DIR__
);
