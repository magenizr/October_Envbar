<?php
/**
 * Magenizr Envbar
 *
 * @category    Magenizr
 * @package     Magenizr_Envbar
 * @copyright   Copyright (c) 2020 Magenizr (https://www.Magenizr.com)
 * @license        http://www.opensource.org/licenses/mit-license.html  MIT License
 */

return [
    'plugin' => [
        'name' => 'Envbar',
        'description' => 'Envbar allows you to differentiate between environments by adding a custom colored bar above the top navigation.',
    ],
    'settings' => [
        'label' => 'Envbar',
        'description' => 'Manage Envbar settings.',
        'tab_default' => 'Settings',
        'enabled' => 'Enabled',
        'enabled_comment' => 'Enable or disable temporarily.',
        'superuser' => 'Superuser',
        'superuser_comment' => 'Enable for Superusers only.',
        'envs' => 'Environments',
        'env' => 'Environment',
        'color' => 'Color',
    ],
    'permission' => [
        'tab' => 'Envbar',
        'label' => 'Manage Settings',
    ],
];
