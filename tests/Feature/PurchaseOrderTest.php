<?php

use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('明細つきで発注を作成できる', function () {
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->for($supplier)->create();

    $this->post(route('purchase-orders.store'), [
        'supplier_id' => $supplier->id,
        'order_date' => '2026-07-01',
        'items' => [
            ['product_id' => $product->id, 'qty' => 10, 'unit_cost' => 300],
        ],
    ])->assertRedirect();

    $order = PurchaseOrder::latest('id')->first();
    expect($order->items)->toHaveCount(1)
        ->and($order->total)->toBe(3000)
        ->and($order->status)->toBe(PurchaseOrderStatus::Draft);
});

test('発注を入荷にすると在庫が加算される', function () {
    $product = Product::factory()->create(['stock_qty' => 20]);
    $order = PurchaseOrder::factory()->create(['status' => PurchaseOrderStatus::Ordered]);
    $order->items()->create([
        'product_id' => $product->id,
        'qty' => 30,
        'unit_cost' => $product->cost,
    ]);

    $this->patch(route('purchase-orders.status', $order), [
        'status' => PurchaseOrderStatus::Received->value,
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe(PurchaseOrderStatus::Received)
        ->and($product->fresh()->stock_qty)->toBe(50);
});

test('下書きから直接入荷にはできない', function () {
    $order = PurchaseOrder::factory()->create();

    $this->patch(route('purchase-orders.status', $order), [
        'status' => PurchaseOrderStatus::Received->value,
    ])->assertSessionHasErrors('status');

    expect($order->fresh()->status)->toBe(PurchaseOrderStatus::Draft);
});

test('発注キャンセルでは在庫は変動しない', function () {
    $product = Product::factory()->create(['stock_qty' => 20]);
    $order = PurchaseOrder::factory()->create(['status' => PurchaseOrderStatus::Ordered]);
    $order->items()->create([
        'product_id' => $product->id,
        'qty' => 30,
        'unit_cost' => $product->cost,
    ]);

    $this->patch(route('purchase-orders.status', $order), ['status' => 'cancelled'])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($product->fresh()->stock_qty)->toBe(20);
});
