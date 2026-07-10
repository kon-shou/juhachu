<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatYen } from '@/lib/orders';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Product {
    id: number;
    sku: string;
    name: string;
    price: number;
    cost: number;
    stock_qty: number;
    supplier_id: number;
    supplier: { id: number; name: string };
}

defineProps<{
    products: Product[];
    suppliers: { id: number; name: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: '商品', href: '/products' }];

const dialogOpen = ref(false);
const editing = ref<Product | null>(null);

const form = useForm({
    sku: '',
    name: '',
    price: 0,
    cost: 0,
    stock_qty: 0,
    supplier_id: null as number | null,
});

function openCreate() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
}

function openEdit(product: Product) {
    editing.value = product;
    form.sku = product.sku;
    form.name = product.name;
    form.price = product.price;
    form.cost = product.cost;
    form.stock_qty = product.stock_qty;
    form.supplier_id = product.supplier_id;
    form.clearErrors();
    dialogOpen.value = true;
}

function submit() {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            dialogOpen.value = false;
        },
    };

    if (editing.value) {
        form.put(route('products.update', editing.value.id), options);
    } else {
        form.post(route('products.store'), options);
    }
}

function destroy(product: Product) {
    if (confirm(`商品「${product.name}」を削除しますか?`)) {
        form.delete(route('products.destroy', product.id), { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="商品" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">商品</h1>
                <Button @click="openCreate">
                    <Plus class="h-4 w-4" />
                    新規登録
                </Button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-sidebar-border/70 text-left dark:border-sidebar-border">
                            <th class="px-4 py-3 font-medium">SKU</th>
                            <th class="px-4 py-3 font-medium">商品名</th>
                            <th class="px-4 py-3 font-medium">仕入先</th>
                            <th class="px-4 py-3 text-right font-medium">売価</th>
                            <th class="px-4 py-3 text-right font-medium">原価</th>
                            <th class="px-4 py-3 text-right font-medium">在庫</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="product in products"
                            :key="product.id"
                            class="border-b border-sidebar-border/40 last:border-0 dark:border-sidebar-border/40"
                        >
                            <td class="px-4 py-3 font-mono text-xs">{{ product.sku }}</td>
                            <td class="px-4 py-3 font-medium">{{ product.name }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ product.supplier.name }}</td>
                            <td class="px-4 py-3 text-right">{{ formatYen(product.price) }}</td>
                            <td class="px-4 py-3 text-right text-muted-foreground">{{ formatYen(product.cost) }}</td>
                            <td class="px-4 py-3 text-right">
                                <span :class="product.stock_qty < 10 ? 'font-semibold text-red-600 dark:text-red-400' : ''">
                                    {{ product.stock_qty }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <Button variant="ghost" size="icon" @click="openEdit(product)">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="destroy(product)">
                                        <Trash2 class="h-4 w-4 text-red-500" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="products.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">商品が登録されていません</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editing ? '商品を編集' : '商品を登録' }}</DialogTitle>
                    <DialogDescription>商品情報を入力してください。</DialogDescription>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="sku">SKU</Label>
                            <Input id="sku" v-model="form.sku" required />
                            <InputError :message="form.errors.sku" />
                        </div>
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
                    </div>
                    <div class="grid gap-2">
                        <Label for="name">商品名</Label>
                        <Input id="name" v-model="form.name" required />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="grid gap-2">
                            <Label for="price">売価</Label>
                            <Input id="price" type="number" min="0" v-model.number="form.price" required />
                            <InputError :message="form.errors.price" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="cost">原価</Label>
                            <Input id="cost" type="number" min="0" v-model.number="form.cost" required />
                            <InputError :message="form.errors.cost" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="stock_qty">在庫数</Label>
                            <Input id="stock_qty" type="number" min="0" v-model.number="form.stock_qty" required />
                            <InputError :message="form.errors.stock_qty" />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="submit" :disabled="form.processing">
                            {{ editing ? '更新' : '登録' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
