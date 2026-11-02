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
        if (! $order->status->canTransitionTo($to)) {
            throw ValidationException::withMessages([
                'status' => "{$order->status->label()} から {$to->label()} へは変更できません。",
            ]);
        }

        return DB::transaction(function () use ($order, $to) {
            if ($to === SalesOrderStatus::Confirmed) {
                $this->allocateStock($order);
            }

            if ($to === SalesOrderStatus::Cancelled && $order->status !== SalesOrderStatus::Draft) {
                $this->releaseStock($order);
            }

            $order->update(['status' => $to]);

            return $order;
        });
    }

    /**
     * 発注のステータスを遷移させる。入荷時に在庫を加算する。
     */
    public function transitionPurchaseOrder(PurchaseOrder $order, PurchaseOrderStatus $to): PurchaseOrder
    {
        if (! $order->status->canTransitionTo($to)) {
            throw ValidationException::withMessages([
                'status' => "{$order->status->label()} から {$to->label()} へは変更できません。",
            ]);
        }

        return DB::transaction(function () use ($order, $to) {
            if ($to === PurchaseOrderStatus::Received) {
                foreach ($order->items as $item) {
                    Product::whereKey($item->product_id)->decrement('stock_qty', $item->qty);
                }
            }

            $order->update(['status' => $to]);

            return $order;
        });
    }

    private function allocateStock(SalesOrder $order): void
    {
        foreach ($order->items()->with('product')->get() as $item) {
            $product = Product::whereKey($item->product_id)->lockForUpdate()->first();

            if ($product->stock_qty < $item->qty) {
                throw ValidationException::withMessages([
                    'status' => "{$product->name} の在庫が不足しています(在庫: {$product->stock_qty}、必要: {$item->qty})。",
                ]);
            }

            $product->decrement('stock_qty', $item->qty);
        }
    }

    private function releaseStock(SalesOrder $order): void
    {
        foreach ($order->items as $item) {
            Product::whereKey($item->product_id)->increment('stock_qty', $item->qty);
        }
    }
}
