<?php

return [
    'storage_driver' => Stancl\Tenancy\StorageDrivers\DatabaseStorageDriver::class,
    'tenant_model' => App\Models\Tenant::class,
    'id_generator' => Stancl\Tenancy\UUIDGenerator::class,
    'database' => [
        'prefix' => 'tenant',
        'suffix' => '',
    ],
    'redis' => [
        'prefix_key' => 'tenant_id:',
    ],
    'filesystem' => [
        'suffix_base' => 'tenants/',
    ],
    'models' => [
        'tenant' => App\Models\Tenant::class,
    ],
    'routes' => [
        'web' => __DIR__ . '/../routes/tenant.php',
    ],
    'features' => [
        Stancl\Tenancy\Features\TenantConfig::class,
        Stancl\Tenancy\Features\TenantCache::class,
        Stancl\Tenancy\Features\TenantEvents::class,
        Stancl\Tenancy\Features\TenantRoutes::class,
        Stancl\Tenancy\Features\TenantAssets::class,
    ],
    'exempt_domains' => [
        'localhost',
        'admin.localhost',
    ],
];
