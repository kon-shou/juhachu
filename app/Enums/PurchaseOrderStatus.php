<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case Ordered = 'ordered';
    case Received = 'received';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    /**
     * @return list<self>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::Draft => [self::Ordered, self::Cancelled],
            self::Ordered => [self::Received, self::Cancelled],
            self::Received => [self::Closed],
            self::Closed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->transitions(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => '下書き',
            self::Ordered => '発注済み',
            self::Received => '入荷済み',
            self::Closed => '完了',
            self::Cancelled => 'キャンセル',
        };
    }
}
