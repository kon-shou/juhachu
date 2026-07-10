<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatYen } from '@/lib/orders';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

interface ProductOption {
    id: number;
    sku: string;
    name: string;
    cost: number;
    stock_qty: number;
    supplier_id: number;
}

const props = defineProps<{
    suppliers: { id: number; name: string }[];
    products: ProductOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: '発注', href: '/purchase-orders' },
    { title: '新規作成', href: '/purchase-orders/create' },
];

interface ItemRow {
    product_id: number | null;
    qty: number;
    unit_cost: number;
}

const form = useForm({
    supplier_id: null as number | null,
    order_date: new Date().toISOString().slice(0, 10),
    note: '',
    items: [{ product_id: null, qty: 1, unit_cost: 0 }] as ItemRow[],
});

const supplierProducts = computed(() =>
    form.supplier_id ? props.products.filter((p) => p.supplier_id === form.supplier_id) : props.products,
);

function addItem() {
    form.items.push({ product_id: null, qty: 1, unit_cost: 0 });
}

function removeItem(index: number) {
    form.items.splice(index, 1);
}

function onProductChange(item: ItemRow) {
    const product = props.products.find((p) => p.id === item.product_id);
    if (product) {
        item.unit_cost = product.cost;
    }
}

const total = computed(() => form.items.reduce((sum, item) => sum + item.qty * item.unit_cost, 0));

function submit() {
    form.post(route('purchase-orders.store'));
}
</script>

<template>
    <Head title="新規発注" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <form class="flex h-full flex-1 flex-col gap-4 p-4" @submit.prevent="submit">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">新規発注</h1>
                <Button type="submit" :disabled="form.processing">登録</Button>
            </div>

            <div class="grid gap-4 rounded-xl border border-sidebar-border/70 p-4 md:grid-cols-3 dark:border-sidebar-border">
                <div class="grid gap-2">
                    <Label for="supplier">仕入先</Label>
                    <select
                        id="supplier"
                        v-model="form.supplier_id"
                        required
                        class="border-input h-9 rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs dark:bg-input/30"
                    >
                        <option :value="null" disabled>選択してください</option>
                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                            {{ supplier.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.supplier_id" />
                </div>
                <div class="grid gap-2">
                    <Label for="order_date">発注日</Label>
                    <Input id="order_date" type="date" v-model="form.order_date" required />
                    <InputError :message="form.errors.order_date" />
                </div>
                <div class="grid gap-2">
                    <Label for="note">備考</Label>
                    <Input id="note" v-model="form.note" />
                    <InputError :message="form.errors.note" />
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="flex items-center justify-between border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                    <h2 class="font-medium">明細</h2>
                    <Button type="button" variant="outline" size="sm" @click="addItem">
                        <Plus class="h-4 w-4" />
                        行を追加
                    </Button>
                </div>
                <InputError class="px-4 pt-2" :message="form.errors.items" />
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-sidebar-border/70 text-left dark:border-sidebar-border">
                            <th class="px-4 py-2 font-medium">商品</th>
                            <th class="w-24 px-4 py-2 text-right font-medium">数量</th>
                            <th class="w-32 px-4 py-2 text-right font-medium">仕入単価</th>
                            <th class="w-32 px-4 py-2 text-right font-medium">小計</th>
                            <th class="w-12 px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="border-b border-sidebar-border/40 last:border-0 dark:border-sidebar-border/40"
                        >
                            <td class="px-4 py-2">
                                <select
                                    v-model="item.product_id"
                                    required
                                    class="border-input h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs dark:bg-input/30"
                                    @change="onProductChange(item)"
                                >
                                    <option :value="null" disabled>選択してください</option>
                                    <option v-for="product in supplierProducts" :key="product.id" :value="product.id">
                                        {{ product.sku }} — {{ product.name }}(在庫: {{ product.stock_qty }})
                                    </option>
                                </select>
                                <InputError :message="(form.errors as Record<string, string>)[`items.${index}.product_id`]" />
                            </td>
                            <td class="px-4 py-2">
                                <Input type="number" min="1" class="text-right" v-model.number="item.qty" required />
                                <InputError :message="(form.errors as Record<string, string>)[`items.${index}.qty`]" />
                            </td>
                            <td class="px-4 py-2">
                                <Input type="number" min="0" class="text-right" v-model.number="item.unit_cost" required />
                                <InputError :message="(form.errors as Record<string, string>)[`items.${index}.unit_cost`]" />
                            </td>
                            <td class="px-4 py-2 text-right">{{ formatYen(item.qty * item.unit_cost) }}</td>
                            <td class="px-4 py-2">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    :disabled="form.items.length === 1"
                                    @click="removeItem(index)"
                                >
                                    <Trash2 class="h-4 w-4 text-red-500" />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-sidebar-border/70 dark:border-sidebar-border">
                            <td colspan="3" class="px-4 py-3 text-right font-medium">合計</td>
                            <td class="px-4 py-3 text-right text-base font-semibold">{{ formatYen(total) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </form>
    </AppLayout>
</template>
