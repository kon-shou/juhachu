<script setup lang="ts">
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, formatYen, purchaseOrderStatusLabels } from '@/lib/orders';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

interface Order {
    id: number;
    order_number: string;
    status: string;
    order_date: string;
    total: number;
    items_count: number;
    supplier: { id: number; name: string };
}

defineProps<{
    orders: Order[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: '発注', href: '/purchase-orders' }];
</script>

<template>
    <Head title="発注" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">発注</h1>
                <Button as-child>
                    <Link :href="route('purchase-orders.create')">
                        <Plus class="h-4 w-4" />
                        新規発注
                    </Link>
                </Button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-sidebar-border/70 text-left dark:border-sidebar-border">
                            <th class="px-4 py-3 font-medium">発注番号</th>
                            <th class="px-4 py-3 font-medium">仕入先</th>
                            <th class="px-4 py-3 font-medium">発注日</th>
                            <th class="px-4 py-3 font-medium">ステータス</th>
                            <th class="px-4 py-3 text-right font-medium">明細数</th>
                            <th class="px-4 py-3 text-right font-medium">合計</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in orders"
                            :key="order.id"
                            class="border-b border-sidebar-border/40 last:border-0 dark:border-sidebar-border/40"
                        >
                            <td class="px-4 py-3">
                                <Link
                                    :href="route('purchase-orders.show', order.id)"
                                    class="font-mono text-xs font-medium text-blue-600 hover:underline dark:text-blue-400"
                                >
                                    {{ order.order_number }}
                                </Link>
                            </td>
                            <td class="px-4 py-3">{{ order.supplier.name }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ formatDate(order.order_date) }}</td>
                            <td class="px-4 py-3">
                                <StatusBadge :status="order.status" :label="purchaseOrderStatusLabels[order.status]" />
                            </td>
                            <td class="px-4 py-3 text-right">{{ order.items_count }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ formatYen(order.total) }}</td>
                        </tr>
                        <tr v-if="orders.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">発注がありません</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
