<script setup lang="ts">
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, formatYen, salesOrderStatusLabels } from '@/lib/orders';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface OrderItem {
    id: number;
    qty: number;
    unit_price: number;
    product: { id: number; sku: string; name: string; stock_qty: number };
}

interface Order {
    id: number;
    order_number: string;
    status: string;
    order_date: string;
    total: number;
    note: string | null;
    customer: { id: number; name: string; email: string };
    items: OrderItem[];
}

const props = defineProps<{
    order: Order;
    transitions: { value: string; label: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: '受注', href: '/sales-orders' },
    { title: props.order.order_number, href: `/sales-orders/${props.order.id}` },
];

const page = usePage();
const statusError = computed(() => (page.props.errors as Record<string, string>)?.status);

function transition(status: string, label: string) {
    if (confirm(`ステータスを「${label}」に変更しますか?`)) {
        router.patch(route('sales-orders.status', props.order.id), { status }, { preserveScroll: true });
    }
}
</script>

<template>
    <Head :title="order.order_number" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <h1 class="font-mono text-xl font-semibold">{{ order.order_number }}</h1>
                    <StatusBadge :status="order.status" :label="salesOrderStatusLabels[order.status]" />
                </div>
                <div class="flex gap-2">
                    <Button
                        v-for="t in transitions"
                        :key="t.value"
                        :variant="t.value === 'cancelled' ? 'destructive' : 'default'"
                        @click="transition(t.value, t.label)"
                    >
                        {{ t.label }}にする
                    </Button>
                </div>
            </div>

            <p v-if="statusError" class="rounded-md bg-red-50 px-4 py-2 text-sm text-red-600 dark:bg-red-950 dark:text-red-400">
                {{ statusError }}
            </p>

            <div class="grid gap-4 rounded-xl border border-sidebar-border/70 p-4 text-sm md:grid-cols-3 dark:border-sidebar-border">
                <div>
                    <div class="text-muted-foreground">顧客</div>
                    <div class="font-medium">{{ order.customer.name }}</div>
                    <div class="text-muted-foreground">{{ order.customer.email }}</div>
                </div>
                <div>
                    <div class="text-muted-foreground">受注日</div>
                    <div class="font-medium">{{ formatDate(order.order_date) }}</div>
                </div>
                <div>
                    <div class="text-muted-foreground">備考</div>
                    <div class="font-medium">{{ order.note ?? '—' }}</div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-sidebar-border/70 text-left dark:border-sidebar-border">
                            <th class="px-4 py-3 font-medium">SKU</th>
                            <th class="px-4 py-3 font-medium">商品名</th>
                            <th class="px-4 py-3 text-right font-medium">数量</th>
                            <th class="px-4 py-3 text-right font-medium">単価</th>
                            <th class="px-4 py-3 text-right font-medium">小計</th>
                            <th class="px-4 py-3 text-right font-medium">現在庫</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in order.items"
                            :key="item.id"
                            class="border-b border-sidebar-border/40 last:border-0 dark:border-sidebar-border/40"
                        >
                            <td class="px-4 py-3 font-mono text-xs">{{ item.product.sku }}</td>
                            <td class="px-4 py-3 font-medium">{{ item.product.name }}</td>
                            <td class="px-4 py-3 text-right">{{ item.qty }}</td>
                            <td class="px-4 py-3 text-right">{{ formatYen(item.unit_price) }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ formatYen(item.qty * item.unit_price) }}</td>
                            <td class="px-4 py-3 text-right text-muted-foreground">{{ item.product.stock_qty }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-sidebar-border/70 dark:border-sidebar-border">
                            <td colspan="4" class="px-4 py-3 text-right font-medium">合計</td>
                            <td class="px-4 py-3 text-right text-base font-semibold">{{ formatYen(order.total) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
