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

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    address: string | null;
    sales_orders_count: number;
}

defineProps<{
    customers: Customer[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: '顧客', href: '/customers' }];

const dialogOpen = ref(false);
const editing = ref<Customer | null>(null);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
});

function openCreate() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
}

function openEdit(customer: Customer) {
    editing.value = customer;
    form.name = customer.name;
    form.email = customer.email;
    form.phone = customer.phone ?? '';
    form.address = customer.address ?? '';
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
        form.put(route('customers.update', editing.value.id), options);
    } else {
        form.post(route('customers.store'), options);
    }
}

function destroy(customer: Customer) {
    if (confirm(`顧客「${customer.name}」を削除しますか?`)) {
        form.delete(route('customers.destroy', customer.id), { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="顧客" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">顧客</h1>
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
                            <th class="px-4 py-3 font-medium">住所</th>
                            <th class="px-4 py-3 text-right font-medium">受注数</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="customer in customers"
                            :key="customer.id"
                            class="border-b border-sidebar-border/40 last:border-0 dark:border-sidebar-border/40"
                        >
                            <td class="px-4 py-3 font-medium">{{ customer.name }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ customer.email }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ customer.phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ customer.address ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">{{ customer.sales_orders_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <Button variant="ghost" size="icon" @click="openEdit(customer)">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="destroy(customer)">
                                        <Trash2 class="h-4 w-4 text-red-500" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="customers.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">顧客が登録されていません</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editing ? '顧客を編集' : '顧客を登録' }}</DialogTitle>
                    <DialogDescription>顧客情報を入力してください。</DialogDescription>
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
                    <div class="grid gap-2">
                        <Label for="address">住所</Label>
                        <Input id="address" v-model="form.address" />
                        <InputError :message="form.errors.address" />
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
