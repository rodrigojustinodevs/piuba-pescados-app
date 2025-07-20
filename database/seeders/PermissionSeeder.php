<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entities = [
            'alert',
            'biometry',
            'client',
            'company',
            'cost-allocation',
            'feeding',
            'feed-control',
            'financial-transaction',
            'growth-curve',
            'harvest',
            'job',
            'purchase',
            'sale',
            'sensor',
            'settlement',
            'stock',
            'subscription',
            'supplier',
            'tank',
            'transfer',
            'user',
        ];

        $actions = ['create', 'update', 'delete', 'view'];

        $now = Carbon::now();

        $permissions = [];

        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                $permissions[] = [
                    'id'         => (string) Str::uuid(),
                    'name'       => "{$action}-{$entity}",
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('permissions')->insert($permissions);
    }
}
