<?php

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('商品一覧が表示できる', function () {
    Product::factory()->count(3)->create();

    $this->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('products/Index')
            ->has('products', 3)
        );
});

test('商品を登録できる', function () {
    $supplier = Supplier::factory()->create();

    $this->post(route('products.store'), [
        'sku' => 'TEST-0001',
        'name' => 'テスト商品',
        'price' => 1500,
        'cost' => 800,
        'stock_qty' => 50,
        'supplier_id' => $supplier->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('products', ['sku' => 'TEST-0001']);
});

test('SKUが重複する商品は登録できない', function () {
    $product = Product::factory()->create();

    $this->post(route('products.store'), [
        'sku' => $product->sku,
        'name' => '重複SKU',
        'price' => 100,
        'cost' => 50,
        'stock_qty' => 0,
        'supplier_id' => $product->supplier_id,
    ])->assertSessionHasErrors('sku');
});

test('価格に負の値は指定できない', function () {
    $supplier = Supplier::factory()->create();

    $this->post(route('products.store'), [
        'sku' => 'NEG-0001',
        'name' => '負の価格',
        'price' => -100,
        'cost' => 50,
        'stock_qty' => 0,
        'supplier_id' => $supplier->id,
    ])->assertSessionHasErrors('price');
});

test('商品を更新できる', function () {
    $product = Product::factory()->create();

    $this->put(route('products.update', $product), [
        'sku' => $product->sku,
        'name' => '更新後の商品名',
        'price' => 2000,
        'cost' => 1000,
        'stock_qty' => 10,
        'supplier_id' => $product->supplier_id,
    ])->assertRedirect();

    expect($product->fresh())
        ->name->toBe('更新後の商品名')
        ->price->toBe(2000);
});
