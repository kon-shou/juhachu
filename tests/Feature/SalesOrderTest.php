<?php

use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

function makeDraftOrder(int $stock = 100, int $qty = 5): SalesOrder
{
    $product = Product::factory()->create(['stock_qty' => $stock]);
    $order = SalesOrder::factory()->create();
    $order->items()->create([
        'product_id' => $product->id,
        'qty' => $qty,
        'unit_price' => $product->price,
    ]);

    return $order;
}

test('明細つきで受注を作成できる', function () {
    $customer = Customer::factory()->create();
    $products = Product::factory()->count(2)->create();

    $this->post(route('sales-orders.store'), [
        'customer_id' => $customer->id,
        'order_date' => '2026-07-01',
        'items' => [
            ['product_id' => $products[0]->id, 'qty' => 3, 'unit_price' => 1000],
            ['product_id' => $products[1]->id, 'qty' => 2, 'unit_price' => 500],
        ],
    ])->assertRedirect();

    $order = SalesOrder::latest('id')->first();
    expect($order->items)->toHaveCount(2)
        ->and($order->total)->toBe(4000)
        ->and($order->status)->toBe(SalesOrderStatus::Draft);
});

test('明細なしでは受注を作成できない', function () {
    $customer = Customer::factory()->create();

    $this->post(route('sales-orders.store'), [
        'customer_id' => $customer->id,
        'order_date' => '2026-07-01',
        'items' => [],
    ])->assertSessionHasErrors('items');
});

test('受注を確定すると在庫が引き当てられる', function () {
    $order = makeDraftOrder(stock: 100, qty: 5);

    $this->patch(route('sales-orders.status', $order), [
        'status' => SalesOrderStatus::Confirmed->value,
    ])->assertRedirect();

    expect($order->fresh()->status)->toBe(SalesOrderStatus::Confirmed)
        ->and($order->items->first()->product->fresh()->stock_qty)->toBe(95);
});

test('在庫不足の受注は確定できない', function () {
    $order = makeDraftOrder(stock: 3, qty: 5);

    $this->patch(route('sales-orders.status', $order), [
        'status' => SalesOrderStatus::Confirmed->value,
    ])->assertSessionHasErrors('status');

    expect($order->fresh()->status)->toBe(SalesOrderStatus::Draft)
        ->and($order->items->first()->product->fresh()->stock_qty)->toBe(3);
});

test('在庫と同数の受注は確定でき在庫が0になる', function () {
    $order = makeDraftOrder(stock: 5, qty: 5);

    $this->patch(route('sales-orders.status', $order), [
        'status' => SalesOrderStatus::Confirmed->value,
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe(SalesOrderStatus::Confirmed)
        ->and($order->items->first()->product->fresh()->stock_qty)->toBe(0);
});

test('確定済み受注をキャンセルすると在庫が戻る', function () {
    $order = makeDraftOrder(stock: 100, qty: 5);

    $this->patch(route('sales-orders.status', $order), ['status' => 'confirmed']);
    expect($order->items->first()->product->fresh()->stock_qty)->toBe(95);

    $this->patch(route('sales-orders.status', $order), ['status' => 'cancelled'])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe(SalesOrderStatus::Cancelled)
        ->and($order->items->first()->product->fresh()->stock_qty)->toBe(100);
});

test('下書き受注のキャンセルでは在庫は変動しない', function () {
    $order = makeDraftOrder(stock: 100, qty: 5);

    $this->patch(route('sales-orders.status', $order), ['status' => 'cancelled'])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($order->items->first()->product->fresh()->stock_qty)->toBe(100);
});

test('不正なステータス遷移は拒否される', function () {
    $order = makeDraftOrder();

    $this->patch(route('sales-orders.status', $order), [
        'status' => SalesOrderStatus::Shipped->value,
    ])->assertSessionHasErrors('status');

    expect($order->fresh()->status)->toBe(SalesOrderStatus::Draft);
});

test('完了済み受注はステータスを変更できない', function () {
    $order = SalesOrder::factory()->create(['status' => SalesOrderStatus::Closed]);

    $this->patch(route('sales-orders.status', $order), ['status' => 'cancelled'])
        ->assertSessionHasErrors('status');
});
