<?php

namespace Database\Seeders;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);

        $suppliers = collect([
            ['name' => '山田製作所', 'email' => 'info@yamada-mfg.example.jp', 'phone' => '03-1234-5678'],
            ['name' => '佐藤商事', 'email' => 'sales@sato-trading.example.jp', 'phone' => '06-2345-6789'],
            ['name' => '鈴木工業', 'email' => 'contact@suzuki-ind.example.jp', 'phone' => '052-345-6789'],
            ['name' => '田中パーツ', 'email' => 'order@tanaka-parts.example.jp', 'phone' => '045-456-7890'],
        ])->map(fn (array $attrs) => Supplier::create($attrs));

        $customers = collect([
            ['name' => '株式会社アオバ', 'email' => 'purchase@aoba.example.jp', 'phone' => '03-1111-2222', 'address' => '東京都千代田区丸の内1-1-1'],
            ['name' => 'ミドリ物産株式会社', 'email' => 'buy@midori.example.jp', 'phone' => '06-3333-4444', 'address' => '大阪府大阪市北区梅田2-2-2'],
            ['name' => '有限会社サクラ電機', 'email' => 'order@sakura-denki.example.jp', 'phone' => '052-555-6666', 'address' => '愛知県名古屋市中区栄3-3-3'],
            ['name' => 'カエデ産業株式会社', 'email' => 'procurement@kaede.example.jp', 'phone' => '092-777-8888', 'address' => '福岡県福岡市博多区博多駅前4-4-4'],
            ['name' => '株式会社ツバキ商会', 'email' => 'info@tsubaki.example.jp', 'phone' => '011-999-0000', 'address' => '北海道札幌市中央区大通西5-5-5'],
            ['name' => 'ヒナタ株式会社', 'email' => 'keiri@hinata.example.jp', 'phone' => '022-123-4567', 'address' => '宮城県仙台市青葉区中央6-6-6'],
            ['name' => '株式会社コハル製作所', 'email' => 'soumu@koharu.example.jp', 'phone' => '075-234-5678', 'address' => '京都府京都市下京区四条通7-7-7'],
            ['name' => 'アカネ工業株式会社', 'email' => 'shizai@akane.example.jp', 'phone' => '082-345-6789', 'address' => '広島県広島市中区紙屋町8-8-8'],
            ['name' => '株式会社スミレ設備', 'email' => 'hanbai@sumire.example.jp', 'phone' => '098-456-7890', 'address' => '沖縄県那覇市久茂地9-9-9'],
            ['name' => 'ワカバ商店', 'email' => 'wakaba@wakaba-shop.example.jp', 'phone' => '026-567-8901', 'address' => '長野県長野市南長野10-10-10'],
        ])->map(fn (array $attrs) => Customer::create($attrs));

        $productNames = [
            'ステンレスボルト M6', 'ステンレスナット M6', 'アルミフレーム 40cm', '樹脂パネル A4',
            'ベアリング 608ZZ', 'ゴムパッキン 30mm', 'LEDモジュール 12V', '電源ユニット 24V',
            'ケーブルハーネス 1m', '制御基板 v2', 'モータードライバ', 'ステッピングモーター',
            'タイミングベルト 2GT', 'プーリー 20T', 'リニアシャフト 8mm', 'シャフトホルダー',
            'カップリング 5x8', 'エンドストップスイッチ', '冷却ファン 40mm', 'ヒートシンク 大',
        ];

        $products = collect($productNames)->map(function (string $name, int $i) use ($suppliers) {
            $cost = fake()->numberBetween(80, 4000);

            return Product::create([
                'sku' => sprintf('PRD-%04d', $i + 1),
                'name' => $name,
                'cost' => $cost,
                'price' => (int) round($cost * fake()->randomFloat(2, 1.3, 2.2)),
                'stock_qty' => fake()->numberBetween(0, 150),
                'supplier_id' => $suppliers->random()->id,
            ]);
        });

        foreach (range(1, 15) as $i) {
            $order = SalesOrder::create([
                'order_number' => sprintf('SO-2026%04d-%04d', $i, $i),
                'customer_id' => $customers->random()->id,
                'status' => fake()->randomElement([
                    SalesOrderStatus::Draft,
                    SalesOrderStatus::Confirmed,
                    SalesOrderStatus::Shipped,
                    SalesOrderStatus::Closed,
                ]),
                'order_date' => fake()->dateTimeBetween('-2 months'),
            ]);

            $total = 0;
            foreach ($products->random(fake()->numberBetween(1, 4)) as $product) {
                $qty = fake()->numberBetween(1, 10);
                $order->items()->create([
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'unit_price' => $product->price,
                ]);
                $total += $qty * $product->price;
            }
            $order->update(['total' => $total]);
        }

        foreach (range(1, 15) as $i) {
            $supplier = $suppliers->random();
            $order = PurchaseOrder::create([
                'order_number' => sprintf('PO-2026%04d-%04d', $i, $i),
                'supplier_id' => $supplier->id,
                'status' => fake()->randomElement([
                    PurchaseOrderStatus::Draft,
                    PurchaseOrderStatus::Ordered,
                    PurchaseOrderStatus::Received,
                    PurchaseOrderStatus::Closed,
                ]),
                'order_date' => fake()->dateTimeBetween('-2 months'),
            ]);

            $supplierProducts = $products->where('supplier_id', $supplier->id);
            if ($supplierProducts->isEmpty()) {
                $supplierProducts = $products;
            }

            $total = 0;
            foreach ($supplierProducts->random(min(3, $supplierProducts->count())) as $product) {
                $qty = fake()->numberBetween(5, 30);
                $order->items()->create([
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'unit_cost' => $product->cost,
                ]);
                $total += $qty * $product->cost;
            }
            $order->update(['total' => $total]);
        }
    }
}
