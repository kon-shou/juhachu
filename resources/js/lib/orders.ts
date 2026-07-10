export const salesOrderStatusLabels: Record<string, string> = {
    draft: '下書き',
    confirmed: '確定',
    shipped: '出荷済み',
    closed: '完了',
    cancelled: 'キャンセル',
};

export const purchaseOrderStatusLabels: Record<string, string> = {
    draft: '下書き',
    ordered: '発注済み',
    received: '入荷済み',
    closed: '完了',
    cancelled: 'キャンセル',
};

export const statusBadgeClasses: Record<string, string> = {
    draft: 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300',
    confirmed: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    ordered: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    shipped: 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
    received: 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
    closed: 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300',
    cancelled: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
};

export function formatYen(amount: number): string {
    return new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(amount);
}

export function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('ja-JP');
}
