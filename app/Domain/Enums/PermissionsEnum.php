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
                self::VIEW_PRODUCT,
                self::VIEW_CUSTOMER,
                self::CREATE_CUSTOMER,
                self::EDIT_CUSTOMER,
                self::VIEW_INVENTORY,
            ],

            RolesEnum::MANAGER => [
                self::VIEW_DASHBOARD,
                self::VIEW_SALE,
                self::CREATE_SALE,
                self::EDIT_SALE,
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
                self::DELETE_SALE,
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
            str_contains($this->value, 'role')
                || str_contains($this->value, 'permission') => 'Access Control',
            default                                         => 'General',
        };
    }
}
