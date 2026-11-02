<?php

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Console\Command;

test('閾値未満の商品があれば仕入先つきで一覧表示し終了コード1を返す', function () {
    $supplier = Supplier::factory()->create(['name' => '山田商事', 'email' => 'yamada@example.com']);
    Product::factory()->for($supplier)->create(['sku' => 'LOW-0001', 'stock_qty' => 3]);
    Product::factory()->for($supplier)->create(['sku' => 'OK-0001', 'stock_qty' => 10]);

    $this->artisan('stock:alert')
        ->expectsOutput('在庫が 10 未満の商品が 1 件あります。')
        ->expectsTable(
            ['SKU', '商品名', '在庫数', '仕入先', '仕入先メール'],
            [['LOW-0001', Product::where('sku', 'LOW-0001')->value('name'), 3, '山田商事', 'yamada@example.com']],
        )
        ->assertExitCode(Command::FAILURE);
});

test('閾値未満の商品がなければ終了コード0を返す', function () {
    Product::factory()->create(['stock_qty' => 10]);

    $this->artisan('stock:alert')
        ->expectsOutput('在庫が 10 未満の商品はありません。')
        ->assertExitCode(Command::SUCCESS);
});

test('--threshold で閾値を変えられる', function () {
    Product::factory()->create(['stock_qty' => 30]);
    Product::factory()->create(['stock_qty' => 60]);

    $this->artisan('stock:alert', ['--threshold' => 50])
        ->expectsOutput('在庫が 50 未満の商品が 1 件あります。')
        ->assertExitCode(Command::FAILURE);
});

test('--threshold が0以上の整数でなければ終了コード2を返す', function (string $threshold) {
    $this->artisan('stock:alert', ['--threshold' => $threshold])
        ->expectsOutput('--threshold には 0 以上の整数を指定してください。')
        ->assertExitCode(Command::INVALID);
})->with(['abc', '-5', '1.5', '']);
