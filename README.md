# juhachu — 受発注管理アプリ

Laravel 12 + Inertia v2 + Vue 3(TypeScript / Tailwind / shadcn-vue)による受発注管理のデモアプリ。
AIコーディングエージェントの協同作業(herdr)や、PRレビューツール(hunk / Zed)の練習台として使うことを想定している。

## 機能

- 顧客・仕入先・商品マスタの CRUD
- 受注(下書き → 確定 → 出荷済み → 完了 / キャンセル)
  - 確定時に在庫を引当、確定後キャンセルで在庫を戻す
- 発注(下書き → 発注済み → 入荷済み → 完了 / キャンセル)
  - 入荷時に在庫を加算
- ダッシュボード(対応中の受発注、受注総額、在庫僅少一覧)

## セットアップ

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

## 起動

```bash
composer run dev   # サーバー + Vite + キュー + ログをまとめて起動
```

ログイン: `test@example.com` / `password`

## テスト

```bash
php artisan test
```

## 主要な構成

| パス | 内容 |
| --- | --- |
| `app/Enums/` | 受注・発注ステータスと遷移ルール |
| `app/Services/InventoryService.php` | ステータス遷移と在庫引当・加算 |
| `app/Http/Controllers/` | Inertia レスポンスを返すコントローラ |
| `resources/js/pages/` | Vue ページ(customers / products / sales-orders など) |
| `tests/Feature/` | Pest による Feature テスト |
| `routes/web.php` | 画面のルート定義 |
| `routes/auth.php` | ログイン・登録・パスワード再設定などの認証のルート定義 |
| `routes/settings.php` | プロフィール・パスワード・外観の設定画面のルート定義 |
| `routes/console.php` | Artisan コマンドの定義 |
