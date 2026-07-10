<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('suppliers/Index', [
            'suppliers' => Supplier::withCount(['products', 'purchaseOrders'])->orderBy('name')->get(),
        ]);
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated());

        return back()->with('success', '仕入先を登録しました。');
    }

    public function update(StoreSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return back()->with('success', '仕入先を更新しました。');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->products()->exists() || $supplier->purchaseOrders()->exists()) {
            return back()->withErrors(['supplier' => '商品または発注が存在する仕入先は削除できません。']);
        }

        $supplier->delete();

        return back()->with('success', '仕入先を削除しました。');
    }
}
