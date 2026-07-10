<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseOrderStatus;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseOrderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('purchase-orders/Index', [
            'orders' => PurchaseOrder::with('supplier:id,name')
                ->withCount('items')
                ->latest('order_date')
                ->latest('id')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('purchase-orders/Create', [
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'products' => Product::with('supplier:id,name')
                ->orderBy('sku')
                ->get(['id', 'sku', 'name', 'cost', 'stock_qty', 'supplier_id']),
        ]);
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $order = DB::transaction(function () use ($data) {
            $order = PurchaseOrder::create([
                'order_number' => PurchaseOrder::nextOrderNumber(),
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'],
                'note' => $data['note'] ?? null,
                'total' => collect($data['items'])->sum(fn ($item) => $item['qty'] * $item['unit_cost']),
            ]);

            $order->items()->createMany($data['items']);

            return $order;
        });

        return redirect()
            ->route('purchase-orders.show', $order)
            ->with('success', "発注 {$order->order_number} を作成しました。");
    }

    public function show(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load(['supplier', 'items.product:id,sku,name,stock_qty']);

        return Inertia::render('purchase-orders/Show', [
            'order' => $purchaseOrder,
            'transitions' => collect($purchaseOrder->status->transitions())
                ->map(fn (PurchaseOrderStatus $status) => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ])
                ->values(),
        ]);
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder, InventoryService $inventory): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(PurchaseOrderStatus::class)],
        ]);

        $inventory->transitionPurchaseOrder($purchaseOrder, PurchaseOrderStatus::from($validated['status']));

        return back()->with('success', 'ステータスを更新しました。');
    }
}
