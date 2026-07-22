<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum PermissionsEnum: string
{
    // ─── Dashboard ───────────────────────────────────────────────────────────
    case VIEW_DASHBOARD = 'view-dashboard';

    // ─── Batches ─────────────────────────────────────────────────────────────
    case VIEW_BATCH       = 'view-batch';
    case CREATE_BATCH     = 'create-batch';
    case UPDATE_BATCH     = 'update-batch';
    case DELETE_BATCH     = 'delete-batch';
    case FINISH_BATCH     = 'finish-batch';
    case DISTRIBUTE_BATCH = 'distribute-batch';

    // ─── Users ───────────────────────────────────────────────────────────────
    case VIEW_USER        = 'view-user';
    case CREATE_USER      = 'create-user';
    case EDIT_USER        = 'edit-user';
    case DELETE_USER      = 'delete-user';
    case ASSIGN_USER_ROLE = 'assign-user-role';

    // ─── Companies ───────────────────────────────────────────────────────────
    case VIEW_COMPANY            = 'view-company';
    case CREATE_COMPANY          = 'create-company';
    case EDIT_COMPANY            = 'edit-company';
    case DELETE_COMPANY          = 'delete-company';
    case MANAGE_COMPANY_SETTINGS = 'manage-company-settings';

    // ─── Sales ───────────────────────────────────────────────────────────────
    case VIEW_SALE    = 'view-sale';
    case CREATE_SALE  = 'create-sale';
    case EDIT_SALE    = 'edit-sale';
    case DELETE_SALE  = 'delete-sale';
    case APPROVE_SALE = 'approve-sale';
    case CANCEL_SALE  = 'cancel-sale';
    case EXPORT_SALE  = 'export-sale';

    // ─── Products ────────────────────────────────────────────────────────────
    case VIEW_PRODUCT         = 'view-product';
    case CREATE_PRODUCT       = 'create-product';
    case EDIT_PRODUCT         = 'edit-product';
    case DELETE_PRODUCT       = 'delete-product';
    case MANAGE_PRODUCT_PRICE = 'manage-product-price';
    case MANAGE_PRODUCT_STOCK = 'manage-product-stock';

    // ─── Reports ─────────────────────────────────────────────────────────────
    case VIEW_REPORT           = 'view-report';
    case EXPORT_REPORT         = 'export-report';
    case VIEW_FINANCIAL_REPORT = 'view-financial-report';

    // ─── Settings ────────────────────────────────────────────────────────────
    case MANAGE_SETTINGS    = 'manage-settings';
    case MANAGE_ROLES       = 'manage-roles';
    case MANAGE_PERMISSIONS = 'manage-permissions';

    // ─── Customers ───────────────────────────────────────────────────────────
    case VIEW_CUSTOMER   = 'view-customer';
    case CREATE_CUSTOMER = 'create-customer';
    case EDIT_CUSTOMER   = 'edit-customer';
    case DELETE_CUSTOMER = 'delete-customer';

    // ─── Inventory ───────────────────────────────────────────────────────────
    case VIEW_INVENTORY   = 'view-inventory';
    case MANAGE_INVENTORY = 'manage-inventory';

    // ─── Finance ─────────────────────────────────────────────────────────────
    case VIEW_FINANCE    = 'view-finance';
    case MANAGE_FINANCE  = 'manage-finance';
    case APPROVE_PAYMENT = 'approve-payment';

    // ─── Audit ───────────────────────────────────────────────────────────────
    case VIEW_AUDIT_LOG = 'view-audit-log';

    // ─── Sensors (aquicultura) ─────────────────────────────────────────────────
    case VIEW_SENSOR   = 'view-sensor';
    case CREATE_SENSOR = 'create-sensor';
    case UPDATE_SENSOR = 'update-sensor';
    case DELETE_SENSOR = 'delete-sensor';

    // ─── Sensor Readings (aquicultura) ─────────────────────────────────────────
    case VIEW_SENSOR_READING   = 'view-sensor-reading';
    case CREATE_SENSOR_READING = 'create-sensor-reading';
    case UPDATE_SENSOR_READING = 'update-sensor-reading';
    case DELETE_SENSOR_READING = 'delete-sensor-reading';

    // ─── Tanks (aquicultura) ─────────────────────────────────────────────────
    case VIEW_TANK   = 'view-tank';
    case CREATE_TANK = 'create-tank';
    case UPDATE_TANK = 'update-tank';
    case DELETE_TANK = 'delete-tank';

    // ─── Biometry (aquicultura) ────────────────────────────────────────────────
    case VIEW_BIOMETRY   = 'view-biometry';
    case CREATE_BIOMETRY = 'create-biometry';
    case UPDATE_BIOMETRY = 'update-biometry';
    case DELETE_BIOMETRY = 'delete-biometry';

    // ─── Mortality (aquicultura) ───────────────────────────────────────────────
    case VIEW_MORTALITY   = 'view-mortality';
    case CREATE_MORTALITY = 'create-mortality';
    case UPDATE_MORTALITY = 'update-mortality';
    case DELETE_MORTALITY = 'delete-mortality';

    // ─── Feed Inventory (aquicultura) ──────────────────────────────────────────
    case VIEW_FEED_INVENTORY   = 'view-feed-inventory';
    case CREATE_FEED_INVENTORY = 'create-feed-inventory';
    case UPDATE_FEED_INVENTORY = 'update-feed-inventory';
    case DELETE_FEED_INVENTORY = 'delete-feed-inventory';

    // ─── Tank History (aquicultura) ────────────────────────────────────────────
    case VIEW_TANK_HISTORY   = 'view-tank-history';
    case CREATE_TANK_HISTORY = 'create-tank-history';

    // ─── Stocking History (aquicultura) ────────────────────────────────────────
    case VIEW_STOCKING_HISTORY   = 'view-stocking-history';
    case CREATE_STOCKING_HISTORY = 'create-stocking-history';

    // ─── Stock (estoque de insumos) ────────────────────────────────────────────
    case VIEW_STOCK   = 'view-stock';
    case CREATE_STOCK = 'create-stock';
    case UPDATE_STOCK = 'update-stock';
    case DELETE_STOCK = 'delete-stock';

    // ─── Purchases (compras) ───────────────────────────────────────────────────
    case VIEW_PURCHASE   = 'view-purchase';
    case CREATE_PURCHASE = 'create-purchase';
    case UPDATE_PURCHASE = 'update-purchase';
    case DELETE_PURCHASE = 'delete-purchase';

    // ─── Financial Transactions (financeiro) ───────────────────────────────────
    case VIEW_FINANCIAL_TRANSACTION   = 'view-financial-transaction';
    case CREATE_FINANCIAL_TRANSACTION = 'create-financial-transaction';
    case UPDATE_FINANCIAL_TRANSACTION = 'update-financial-transaction';
    case DELETE_FINANCIAL_TRANSACTION = 'delete-financial-transaction';

    // ─── Financial Categories (financeiro) ─────────────────────────────────────
    case VIEW_FINANCIAL_CATEGORY   = 'view-financial-category';
    case CREATE_FINANCIAL_CATEGORY = 'create-financial-category';
    case UPDATE_FINANCIAL_CATEGORY = 'update-financial-category';
    case DELETE_FINANCIAL_CATEGORY = 'delete-financial-category';

    // ─── Clients (clientes) ────────────────────────────────────────────────────
    case VIEW_CLIENT   = 'view-client';
    case CREATE_CLIENT = 'create-client';
    case UPDATE_CLIENT = 'update-client';
    case DELETE_CLIENT = 'delete-client';

    // ─── Alerts ──────────────────────────────────────────────────────────────
    case VIEW_ALERT   = 'view-alert';
    case CREATE_ALERT = 'create-alert';
    case UPDATE_ALERT = 'update-alert';
    case DELETE_ALERT = 'delete-alert';

    // ─── Cost Allocation (financeiro) ──────────────────────────────────────────
    case VIEW_COST_ALLOCATION   = 'view-cost-allocation';
    case CREATE_COST_ALLOCATION = 'create-cost-allocation';
    case DELETE_COST_ALLOCATION = 'delete-cost-allocation';

    // ─── Feeding (aquicultura) ─────────────────────────────────────────────────
    case VIEW_FEEDING   = 'view-feeding';
    case CREATE_FEEDING = 'create-feeding';
    case UPDATE_FEEDING = 'update-feeding';
    case DELETE_FEEDING = 'delete-feeding';

    // ─── Growth Curve (aquicultura) ────────────────────────────────────────────
    case VIEW_GROWTH_CURVE   = 'view-growth-curve';
    case CREATE_GROWTH_CURVE = 'create-growth-curve';
    case UPDATE_GROWTH_CURVE = 'update-growth-curve';
    case DELETE_GROWTH_CURVE = 'delete-growth-curve';

    // ─── Harvest (aquicultura) ─────────────────────────────────────────────────
    case VIEW_HARVEST   = 'view-harvest';
    case CREATE_HARVEST = 'create-harvest';
    case UPDATE_HARVEST = 'update-harvest';
    case DELETE_HARVEST = 'delete-harvest';

    // ─── Sale Orders (pedidos/cotações) ────────────────────────────────────────
    case VIEW_SALE_ORDER   = 'view-sale-order';
    case CREATE_SALE_ORDER = 'create-sale-order';
    case UPDATE_SALE_ORDER = 'update-sale-order';
    case DELETE_SALE_ORDER = 'delete-sale-order';
    case CANCEL_SALE_ORDER = 'cancel-sale-order';

    // ─── Sale (alias de verbo — a rota usa update-sale, não edit-sale) ─────────
    case UPDATE_SALE = 'update-sale';

    /**
     * Retorna todas as permissions que um determinado role possui por padrão.
     *
     * @return array<self>
     */
    public static function forRole(RolesEnum $role): array
    {
        return match ($role) {
            RolesEnum::GUEST => [
                self::VIEW_DASHBOARD,
            ],

            RolesEnum::OPERATOR => [
                self::VIEW_DASHBOARD,
                self::VIEW_SALE,
                self::CREATE_SALE,
                self::EDIT_SALE,
                self::UPDATE_SALE,
                self::VIEW_PRODUCT,
                self::VIEW_CUSTOMER,
                self::CREATE_CUSTOMER,
                self::EDIT_CUSTOMER,
                self::VIEW_INVENTORY,
                self::VIEW_TANK,
                self::VIEW_SENSOR,
                self::VIEW_SENSOR_READING,

                // Operações de campo (aquicultura) — operador registra dados
                self::VIEW_BATCH,
                self::CREATE_BATCH,
                self::VIEW_BIOMETRY,
                self::CREATE_BIOMETRY,
                self::VIEW_MORTALITY,
                self::CREATE_MORTALITY,
                self::VIEW_FEED_INVENTORY,
                self::CREATE_FEED_INVENTORY,
                self::VIEW_TANK_HISTORY,
                self::CREATE_TANK_HISTORY,
                self::VIEW_STOCKING_HISTORY,
                self::CREATE_STOCKING_HISTORY,
                self::VIEW_STOCK,
                self::VIEW_PURCHASE,
                self::VIEW_FEEDING,
                self::CREATE_FEEDING,
                self::VIEW_GROWTH_CURVE,
                self::CREATE_GROWTH_CURVE,
                self::VIEW_HARVEST,
                self::VIEW_CLIENT,
                self::CREATE_CLIENT,
                self::UPDATE_CLIENT,
                self::VIEW_ALERT,
                self::VIEW_SALE_ORDER,
                self::CREATE_SALE_ORDER,
                self::UPDATE_SALE_ORDER,
            ],

            RolesEnum::MANAGER => [
                self::VIEW_DASHBOARD,
                self::VIEW_SALE,
                self::CREATE_SALE,
                self::EDIT_SALE,
                self::UPDATE_SALE,
                self::CANCEL_SALE,
                self::APPROVE_SALE,
                self::EXPORT_SALE,
                self::VIEW_PRODUCT,
                self::CREATE_PRODUCT,
                self::EDIT_PRODUCT,
                self::MANAGE_PRODUCT_PRICE,
                self::MANAGE_PRODUCT_STOCK,
                self::VIEW_CUSTOMER,
                self::CREATE_CUSTOMER,
                self::EDIT_CUSTOMER,
                self::DELETE_CUSTOMER,
                self::VIEW_INVENTORY,
                self::MANAGE_INVENTORY,
                self::VIEW_REPORT,
                self::EXPORT_REPORT,
                self::VIEW_USER,
                self::VIEW_TANK,
                self::CREATE_TANK,
                self::UPDATE_TANK,
                self::VIEW_SENSOR,
                self::CREATE_SENSOR,
                self::UPDATE_SENSOR,
                self::VIEW_SENSOR_READING,
                self::CREATE_SENSOR_READING,
                self::UPDATE_SENSOR_READING,

                // Operações de campo (aquicultura) — gerente gerencia
                self::VIEW_BATCH,
                self::CREATE_BATCH,
                self::UPDATE_BATCH,
                self::VIEW_BIOMETRY,
                self::CREATE_BIOMETRY,
                self::UPDATE_BIOMETRY,
                self::DELETE_BIOMETRY,
                self::VIEW_MORTALITY,
                self::CREATE_MORTALITY,
                self::UPDATE_MORTALITY,
                self::DELETE_MORTALITY,
                self::VIEW_FEED_INVENTORY,
                self::CREATE_FEED_INVENTORY,
                self::UPDATE_FEED_INVENTORY,
                self::DELETE_FEED_INVENTORY,
                self::VIEW_TANK_HISTORY,
                self::CREATE_TANK_HISTORY,
                self::VIEW_STOCKING_HISTORY,
                self::CREATE_STOCKING_HISTORY,
                self::VIEW_STOCK,
                self::CREATE_STOCK,
                self::UPDATE_STOCK,
                self::VIEW_PURCHASE,
                self::CREATE_PURCHASE,
                self::UPDATE_PURCHASE,
                // Financeiro — gerente apenas visualiza
                self::VIEW_FINANCIAL_TRANSACTION,
                self::VIEW_FINANCIAL_CATEGORY,
                self::VIEW_COST_ALLOCATION,

                self::VIEW_FEEDING,
                self::CREATE_FEEDING,
                self::UPDATE_FEEDING,
                self::DELETE_FEEDING,
                self::VIEW_GROWTH_CURVE,
                self::CREATE_GROWTH_CURVE,
                self::UPDATE_GROWTH_CURVE,
                self::DELETE_GROWTH_CURVE,
                self::VIEW_HARVEST,
                self::CREATE_HARVEST,
                self::UPDATE_HARVEST,
                self::DELETE_HARVEST,

                self::VIEW_CLIENT,
                self::CREATE_CLIENT,
                self::UPDATE_CLIENT,
                self::DELETE_CLIENT,

                self::VIEW_ALERT,
                self::CREATE_ALERT,
                self::UPDATE_ALERT,
                self::DELETE_ALERT,

                self::VIEW_SALE_ORDER,
                self::CREATE_SALE_ORDER,
                self::UPDATE_SALE_ORDER,
                self::DELETE_SALE_ORDER,
                self::CANCEL_SALE_ORDER,
            ],

            RolesEnum::ADMIN => [
                self::VIEW_DASHBOARD,
                self::VIEW_USER,
                self::CREATE_USER,
                self::EDIT_USER,
                self::ASSIGN_USER_ROLE,
                self::VIEW_SALE,
                self::CREATE_SALE,
                self::EDIT_SALE,
                self::UPDATE_SALE,
                self::CANCEL_SALE,
                self::APPROVE_SALE,
                self::EXPORT_SALE,
                self::VIEW_PRODUCT,
                self::CREATE_PRODUCT,
                self::EDIT_PRODUCT,
                self::DELETE_PRODUCT,
                self::MANAGE_PRODUCT_PRICE,
                self::MANAGE_PRODUCT_STOCK,
                self::VIEW_CUSTOMER,
                self::CREATE_CUSTOMER,
                self::EDIT_CUSTOMER,
                self::DELETE_CUSTOMER,
                self::VIEW_INVENTORY,
                self::MANAGE_INVENTORY,
                self::VIEW_REPORT,
                self::EXPORT_REPORT,
                self::VIEW_FINANCIAL_REPORT,
                self::VIEW_FINANCE,
                self::APPROVE_PAYMENT,
                self::MANAGE_SETTINGS,

                self::VIEW_SENSOR,
                self::CREATE_SENSOR,
                self::UPDATE_SENSOR,

                self::VIEW_SENSOR_READING,
                self::CREATE_SENSOR_READING,
                self::UPDATE_SENSOR_READING,
                self::DELETE_SENSOR_READING,

                self::VIEW_TANK,
                self::CREATE_TANK,
                self::UPDATE_TANK,
                self::DELETE_TANK,

                // Operações de campo e financeiro (aquicultura) — admin CRUD completo
                // (delete-batch fica restrito a company_admin — ver bloco COMPANY_ADMIN)
                self::VIEW_BATCH,
                self::CREATE_BATCH,
                self::UPDATE_BATCH,
                self::VIEW_BIOMETRY,
                self::CREATE_BIOMETRY,
                self::UPDATE_BIOMETRY,
                self::DELETE_BIOMETRY,
                self::VIEW_MORTALITY,
                self::CREATE_MORTALITY,
                self::UPDATE_MORTALITY,
                self::DELETE_MORTALITY,
                self::VIEW_FEED_INVENTORY,
                self::CREATE_FEED_INVENTORY,
                self::UPDATE_FEED_INVENTORY,
                self::DELETE_FEED_INVENTORY,
                self::VIEW_TANK_HISTORY,
                self::CREATE_TANK_HISTORY,
                self::VIEW_STOCKING_HISTORY,
                self::CREATE_STOCKING_HISTORY,
                self::VIEW_STOCK,
                self::CREATE_STOCK,
                self::UPDATE_STOCK,
                self::DELETE_STOCK,
                self::VIEW_PURCHASE,
                self::CREATE_PURCHASE,
                self::UPDATE_PURCHASE,
                self::DELETE_PURCHASE,
                self::VIEW_FINANCIAL_TRANSACTION,
                self::CREATE_FINANCIAL_TRANSACTION,
                self::UPDATE_FINANCIAL_TRANSACTION,
                self::DELETE_FINANCIAL_TRANSACTION,
                self::VIEW_FINANCIAL_CATEGORY,
                self::CREATE_FINANCIAL_CATEGORY,
                self::UPDATE_FINANCIAL_CATEGORY,
                self::DELETE_FINANCIAL_CATEGORY,
                self::VIEW_COST_ALLOCATION,
                self::CREATE_COST_ALLOCATION,
                self::DELETE_COST_ALLOCATION,

                self::VIEW_FEEDING,
                self::CREATE_FEEDING,
                self::UPDATE_FEEDING,
                self::DELETE_FEEDING,
                self::VIEW_GROWTH_CURVE,
                self::CREATE_GROWTH_CURVE,
                self::UPDATE_GROWTH_CURVE,
                self::DELETE_GROWTH_CURVE,
                self::VIEW_HARVEST,
                self::CREATE_HARVEST,
                self::UPDATE_HARVEST,
                self::DELETE_HARVEST,

                self::VIEW_CLIENT,
                self::CREATE_CLIENT,
                self::UPDATE_CLIENT,
                self::DELETE_CLIENT,

                self::VIEW_ALERT,
                self::CREATE_ALERT,
                self::UPDATE_ALERT,
                self::DELETE_ALERT,

                self::VIEW_SALE_ORDER,
                self::CREATE_SALE_ORDER,
                self::UPDATE_SALE_ORDER,
                self::DELETE_SALE_ORDER,
                self::CANCEL_SALE_ORDER,
            ],

            RolesEnum::COMPANY_ADMIN => [
                // Herda tudo do ADMIN + gerencia a empresa
                ...self::forRole(RolesEnum::ADMIN),
                self::DELETE_USER,
                self::VIEW_COMPANY,
                self::EDIT_COMPANY,
                self::MANAGE_COMPANY_SETTINGS,
                self::MANAGE_FINANCE,
                self::MANAGE_ROLES,
                self::VIEW_AUDIT_LOG,

                // Ações destrutivas restritas a company_admin+ (gate de role nas Policies)
                self::DELETE_BATCH,
                self::DELETE_SALE,
                self::DELETE_SENSOR,
            ],

            RolesEnum::MASTER_ADMIN => self::cases(), // Todas as permissions
        };
    }

    /** Retorna o label legível da permission */
    public function label(): string
    {
        return ucwords(str_replace('-', ' ', $this->value));
    }

    /** Agrupa por categoria para listagem */
    public function category(): string
    {
        return match (true) {
            str_contains($this->value, 'dashboard') => 'Dashboard',
            str_contains($this->value, 'user')      => 'Users',
            str_contains($this->value, 'company')   => 'Companies',
            str_contains($this->value, 'sale')      => 'Sales',
            str_contains($this->value, 'product')   => 'Products',
            str_contains($this->value, 'report')    => 'Reports',
            str_contains($this->value, 'setting')   => 'Settings',
            str_contains($this->value, 'customer')  => 'Customers',
            str_contains($this->value, 'inventory') => 'Inventory',
            str_contains($this->value, 'finance')
                || str_contains($this->value, 'payment') => 'Finance',
            str_contains($this->value, 'audit')          => 'Audit',
            str_contains($this->value, 'sensor')         => 'Sensors',
            str_contains($this->value, 'tank')           => 'Tanks',
            str_contains($this->value, 'role')
                || str_contains($this->value, 'permission') => 'Access Control',
            default                                         => 'General',
        };
    }
}
