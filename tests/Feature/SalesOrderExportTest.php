<?php

use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;

test('受注CSVをダウンロードできる', function () {
    $this->actingAs(User::factory()->create());
    SalesOrder::factory()->count(3)->create();

    $response = $this->get(route('sales-orders.export'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    expect($response->streamedContent())->toContain('受注番号');
});

test('受注CSVに受注の内容が1行ずつ出力される', function () {
    $this->actingAs(User::factory()->create());
    $customer = Customer::factory()->create(['name' => '山田商店']);
    $order = SalesOrder::factory()->create([
        'order_number' => 'SO-20260701-0001',
        'customer_id' => $customer->id,
        'status' => SalesOrderStatus::Confirmed,
        'order_date' => '2026-07-01',
        'total' => 4000,
    ]);
    $product = Product::factory()->create();
    $order->items()->createMany([
        ['product_id' => $product->id, 'qty' => 1, 'unit_price' => 1000],
        ['product_id' => $product->id, 'qty' => 3, 'unit_price' => 1000],
    ]);

    $lines = array_values(array_filter(explode("\n", $this->get(route('sales-orders.export'))->streamedContent())));

    expect($lines)->toHaveCount(2)
        ->and(str_getcsv($lines[1]))->toBe(['SO-20260701-0001', '山田商店', '2026-07-01', '確定', '2', '4000']);
});

test('未ログインでは受注CSVをダウンロードできない', function () {
    $this->get(route('sales-orders.export'))->assertRedirect(route('login'));
});
