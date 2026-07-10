<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 10;

    public function __invoke(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'openSalesOrders' => SalesOrder::whereIn('status', [
                    SalesOrderStatus::Draft,
                    SalesOrderStatus::Confirmed,
                ])->count(),
                'openPurchaseOrders' => PurchaseOrder::whereIn('status', [
                    PurchaseOrderStatus::Draft,
                    PurchaseOrderStatus::Ordered,
                ])->count(),
                'salesTotal' => (int) SalesOrder::whereNot('status', SalesOrderStatus::Cancelled)->sum('total'),
                'productCount' => Product::count(),
            ],
            'lowStockProducts' => Product::with('supplier:id,name')
                ->where('stock_qty', '<', self::LOW_STOCK_THRESHOLD)
                ->orderBy('stock_qty')
                ->get(['id', 'sku', 'name', 'stock_qty', 'supplier_id']),
            'recentSalesOrders' => SalesOrder::with('customer:id,name')
                ->latest('id')
                ->limit(5)
                ->get(),
        ]);
    }
}
