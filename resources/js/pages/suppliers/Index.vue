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
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Supplier {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    products_count: number;
    purchase_orders_count: number;
}

defineProps<{
    suppliers: Supplier[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: '仕入先', href: '/suppliers' }];

const dialogOpen = ref(false);
const editing = ref<Supplier | null>(null);

const form = useForm({
    name: '',
    email: '',
    phone: '',
});

function openCreate() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
}

function openEdit(supplier: Supplier) {
    editing.value = supplier;
    form.name = supplier.name;
    form.email = supplier.email;
    form.phone = supplier.phone ?? '';
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
        form.put(route('suppliers.update', editing.value.id), options);
    } else {
        form.post(route('suppliers.store'), options);
    }
}

function destroy(supplier: Supplier) {
    if (confirm(`仕入先「${supplier.name}」を削除しますか?`)) {
        form.delete(route('suppliers.destroy', supplier.id), { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="仕入先" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">仕入先</h1>
                <Button @click="openCreate">
                    <Plus class="h-4 w-4" />
                    新規登録
                </Button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-sidebar-border/70 text-left dark:border-sidebar-border">
                            <th class="px-4 py-3 font-medium">名前</th>
                            <th class="px-4 py-3 font-medium">メール</th>
                            <th class="px-4 py-3 font-medium">電話</th>
                            <th class="px-4 py-3 text-right font-medium">商品数</th>
                            <th class="px-4 py-3 text-right font-medium">発注数</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="supplier in suppliers"
                            :key="supplier.id"
                            class="border-b border-sidebar-border/40 last:border-0 dark:border-sidebar-border/40"
                        >
                            <td class="px-4 py-3 font-medium">{{ supplier.name }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ supplier.email }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ supplier.phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">{{ supplier.products_count }}</td>
                            <td class="px-4 py-3 text-right">{{ supplier.purchase_orders_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <Button variant="ghost" size="icon" @click="openEdit(supplier)">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="destroy(supplier)">
                                        <Trash2 class="h-4 w-4 text-red-500" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="suppliers.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">仕入先が登録されていません</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editing ? '仕入先を編集' : '仕入先を登録' }}</DialogTitle>
                    <DialogDescription>仕入先情報を入力してください。</DialogDescription>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="name">名前</Label>
                        <Input id="name" v-model="form.name" required />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="email">メールアドレス</Label>
                        <Input id="email" type="email" v-model="form.email" required />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="phone">電話番号</Label>
                        <Input id="phone" v-model="form.phone" />
                        <InputError :message="form.errors.phone" />
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
