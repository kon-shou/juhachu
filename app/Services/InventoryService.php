<?php

namespace App\Services;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    /**
     * 受注のステータスを遷移させる。確定時に在庫を引き当て、
     * 確定後のキャンセル時は在庫を戻す。
     */
    public function transitionSalesOrder(SalesOrder $order, SalesOrderStatus $to): SalesOrder
    {
        $this->assertTransition($order->status->canTransitionTo($to), $order->status->label(), $to->label());

        return DB::transaction(function () use ($order, $to) {
            match (true) {
                $to === SalesOrderStatus::Confirmed => $this->allocateStock($order),
                $to === SalesOrderStatus::Cancelled && $order->status !== SalesOrderStatus::Draft => $this->adjustStockForItems($order, +1),
                default => null,
            };

            $order->update(['status' => $to]);

            return $order;
        });
    }

    /**
     * 発注のステータスを遷移させる。入荷時に在庫を加算する。
     */
    public function transitionPurchaseOrder(PurchaseOrder $order, PurchaseOrderStatus $to): PurchaseOrder
    {
        $this->assertTransition($order->status->canTransitionTo($to), $order->status->label(), $to->label());

        return DB::transaction(function () use ($order, $to) {
            if ($to === PurchaseOrderStatus::Received) {
                $this->adjustStockForItems($order, +1);
            }

            $order->update(['status' => $to]);

            return $order;
        });
    }

    private function allocateStock(SalesOrder $order): void
    {
        // 引当の確認から減算までの間に他の受注が同じ商品を引き当てないよう、行ロックを取ってから在庫を確かめる
        foreach ($order->items as $item) {
            $product = Product::whereKey($item->product_id)->lockForUpdate()->first();

            if ($product->stock_qty < $item->qty) {
                throw ValidationException::withMessages([
                    'status' => "{$product->name} の在庫が不足しています(在庫: {$product->stock_qty}、必要: {$item->qty})。",
                ]);
            }
        }

        $this->adjustStockForItems($order, -1);
    }

    private function adjustStockForItems(SalesOrder|PurchaseOrder $order, int $direction): void
    {
        $order->items->each(function ($item) use ($direction) {
            Product::whereKey($item->product_id)->increment('stock_qty', $direction * $item->qty);
        });
    }

    private function assertTransition(bool $allowed, string $fromLabel, string $toLabel): void
    {
        if (! $allowed) {
            throw ValidationException::withMessages([
                'status' => "{$fromLabel} から {$toLabel} へは変更できません。",
            ]);
        }
    }
}
