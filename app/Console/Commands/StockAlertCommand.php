<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class StockAlertCommand extends Command
{
    protected $signature = 'stock:alert {--threshold=10 : この数量未満の商品を警告対象にする}';

    protected $description = '在庫が僅少な商品を一覧表示する';

    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');

        $products = Product::with('supplier')
            ->where('stock_qty', '<', $threshold)
            ->orderBy('stock_qty')
            ->get();

        if ($products->isEmpty()) {
            $this->info("在庫が {$threshold} 未満の商品はありません。");

            return self::SUCCESS;
        }

        $this->warn("在庫が {$threshold} 未満の商品が {$products->count()} 件あります。");

        $this->table(
            ['SKU', '商品名', '在庫数', '仕入先', '仕入先メール'],
            $products->map(fn (Product $product) => [
                $product->sku,
                $product->name,
                $product->stock_qty,
                $product->supplier->name,
                $product->supplier->email,
            ]),
        );

        return self::FAILURE;
    }
}
