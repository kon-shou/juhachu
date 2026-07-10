<?php

namespace App\Http\Controllers;

use App\Enums\SalesOrderStatus;
use App\Http\Requests\StoreSalesOrderRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SalesOrderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('sales-orders/Index', [
            'orders' => SalesOrder::with('customer:id,name')
                ->withCount('items')
                ->latest('order_date')
                ->latest('id')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('sales-orders/Create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('sku')->get(['id', 'sku', 'name', 'price', 'stock_qty']),
        ]);
    }

    public function store(StoreSalesOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $order = DB::transaction(function () use ($data) {
            $order = SalesOrder::create([
                'order_number' => SalesOrder::nextOrderNumber(),
                'customer_id' => $data['customer_id'],
                'order_date' => $data['order_date'],
                'note' => $data['note'] ?? null,
                'total' => collect($data['items'])->sum(fn ($item) => $item['qty'] * $item['unit_price']),
            ]);

            $order->items()->createMany($data['items']);

            return $order;
        });

        return redirect()
            ->route('sales-orders.show', $order)
            ->with('success', "受注 {$order->order_number} を作成しました。");
    }

    public function show(SalesOrder $salesOrder): Response
    {
        $salesOrder->load(['customer', 'items.product:id,sku,name,stock_qty']);

        return Inertia::render('sales-orders/Show', [
            'order' => $salesOrder,
            'transitions' => collect($salesOrder->status->transitions())
                ->map(fn (SalesOrderStatus $status) => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ])
                ->values(),
        ]);
    }

    public function updateStatus(Request $request, SalesOrder $salesOrder, InventoryService $inventory): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(SalesOrderStatus::class)],
        ]);

        $inventory->transitionSalesOrder($salesOrder, SalesOrderStatus::from($validated['status']));

        return back()->with('success', 'ステータスを更新しました。');
    }
}
