<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Stock;

class SyncProductStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:sync
                            {--warehouse= : Warehouse ID to use (default: first active)}
                            {--force : Force sync even if records exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure all products have stock records in at least one warehouse';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting stock sync...');

        // Determine warehouse
        $warehouseId = $this->option('warehouse');
        if ($warehouseId) {
            $warehouse = Warehouse::find($warehouseId);
            if (!$warehouse) {
                $this->error("Warehouse ID {$warehouseId} not found.");
                return 1;
            }
        } else {
            $warehouse = Warehouse::where('is_active', 1)->first();
            if (!$warehouse) {
                $this->info('No active warehouse found. Creating a default one...');
                $warehouse = Warehouse::create([
                    'name' => 'Main Warehouse',
                    'code' => 'MAIN',
                    'address' => 'Default address',
                    'is_active' => 1,
                ]);
                $this->info("Created warehouse: {$warehouse->name} (ID: {$warehouse->id})");
            }
        }

        $this->info("Using warehouse: {$warehouse->name} (ID: {$warehouse->id})");

        $force = $this->option('force');
        $created = 0;
        $skipped = 0;

        // Process products in chunks
        Product::chunk(100, function ($products) use ($warehouse, $force, &$created, &$skipped) {
            foreach ($products as $product) {
                $stock = Stock::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->first();

                if ($stock && !$force) {
                    $skipped++;
                    continue;
                }

                if ($stock && $force) {
                    // Update existing stock to match product quantity
                    $stock->quantity = $product->quantity ?? 0;
                    $stock->save();
                    $created++;
                } else {
                    // Create new stock record
                    Stock::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                        'quantity' => $product->quantity ?? 0,
                    ]);
                    $created++;
                }
            }
        });

        $this->info("Sync completed.");
        $this->line("Created/Updated: {$created} records");
        $this->line("Skipped: {$skipped} records (already exist)");

        return 0;
    }
}