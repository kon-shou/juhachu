<script setup lang="ts">
import StatusBadge from '@/components/StatusBadge.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, formatYen, salesOrderStatusLabels } from '@/lib/orders';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Banknote, ClipboardList, Package, Truck } from 'lucide-vue-next';

interface LowStockProduct {
    id: number;
    sku: string;
    name: string;
    stock_qty: number;
    supplier: { id: number; name: string };
}

interface RecentOrder {
    id: number;
    order_number: string;
    status: string;
    order_date: string;
    total: number;
    customer: { id: number; name: string };
}

defineProps<{
    stats: {
        openSalesOrders: number;
        openPurchaseOrders: number;
        salesTotal: number;
        productCount: number;
    };
    lowStockProducts: LowStockProduct[];
    recentSalesOrders: RecentOrder[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'ダッシュボード', href: '/dashboard' }];
</script>

<template>
    <Head title="ダッシュボード" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <ClipboardList class="h-4 w-4" />
                        対応中の受注
                    </div>
                    <div class="mt-2 text-3xl font-semibold">{{ stats.openSalesOrders }}</div>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <Truck class="h-4 w-4" />
                        対応中の発注
                    </div>
                    <div class="mt-2 text-3xl font-semibold">{{ stats.openPurchaseOrders }}</div>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <Banknote class="h-4 w-4" />
                        受注総額
                    </div>
                    <div class="mt-2 text-3xl font-semibold">{{ formatYen(stats.salesTotal) }}</div>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <Package class="h-4 w-4" />
                        登録商品数
                    </div>
                    <div class="mt-2 text-3xl font-semibold">{{ stats.productCount }}</div>
                </div>
            </div>

            <div class="grid flex-1 gap-4 lg:grid-cols-2">
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <h2 class="border-b border-sidebar-border/70 px-4 py-3 font-medium dark:border-sidebar-border">最近の受注</h2>
                    <table class="w-full text-sm">
                        <tbody>
                            <tr
                                v-for="order in recentSalesOrders"
                                :key="order.id"
                                class="border-b border-sidebar-border/40 last:border-0 dark:border-sidebar-border/40"
                            >
                                <td class="px-4 py-3">
                                    <Link
                                        :href="route('sales-orders.show', order.id)"
                                        class="font-mono text-xs font-medium text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        {{ order.order_number }}
                                    </Link>
                                </td>
                                <td class="px-4 py-3">{{ order.customer.name }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ formatDate(order.order_date) }}</td>
                                <td class="px-4 py-3">
                                    <StatusBadge :status="order.status" :label="salesOrderStatusLabels[order.status]" />
                                </td>
                                <td class="px-4 py-3 text-right font-medium">{{ formatYen(order.total) }}</td>
                            </tr>
                            <tr v-if="recentSalesOrders.length === 0">
                                <td class="px-4 py-8 text-center text-muted-foreground">受注がありません</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <h2 class="border-b border-sidebar-border/70 px-4 py-3 font-medium dark:border-sidebar-border">在庫僅少(10未満)</h2>
                    <table class="w-full text-sm">
                        <tbody>
                            <tr
                                v-for="product in lowStockProducts"
                                :key="product.id"
                                class="border-b border-sidebar-border/40 last:border-0 dark:border-sidebar-border/40"
                            >
                                <td class="px-4 py-3 font-mono text-xs">{{ product.sku }}</td>
                                <td class="px-4 py-3 font-medium">{{ product.name }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ product.supplier.name }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-semibold text-red-600 dark:text-red-400">{{ product.stock_qty }}</span>
                                </td>
                            </tr>
                            <tr v-if="lowStockProducts.length === 0">
                                <td class="px-4 py-8 text-center text-muted-foreground">在庫僅少の商品はありません</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
