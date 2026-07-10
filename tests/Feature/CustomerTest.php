<?php

use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('顧客一覧が表示できる', function () {
    Customer::factory()->count(3)->create();

    $this->get(route('customers.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('customers/Index')
            ->has('customers', 3)
        );
});

test('顧客を登録できる', function () {
    $this->post(route('customers.store'), [
        'name' => '株式会社テスト',
        'email' => 'test@customer.example.jp',
        'phone' => '03-0000-0000',
        'address' => '東京都テスト区1-1-1',
    ])->assertRedirect();

    $this->assertDatabaseHas('customers', ['email' => 'test@customer.example.jp']);
});

test('メールアドレスが重複する顧客は登録できない', function () {
    Customer::factory()->create(['email' => 'dup@example.jp']);

    $this->post(route('customers.store'), [
        'name' => '重複テスト',
        'email' => 'dup@example.jp',
    ])->assertSessionHasErrors('email');
});

test('顧客を更新できる', function () {
    $customer = Customer::factory()->create();

    $this->put(route('customers.update', $customer), [
        'name' => '更新後の名前',
        'email' => $customer->email,
    ])->assertRedirect();

    expect($customer->fresh()->name)->toBe('更新後の名前');
});

test('受注のない顧客は削除できる', function () {
    $customer = Customer::factory()->create();

    $this->delete(route('customers.destroy', $customer))->assertRedirect();

    $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
});

test('受注のある顧客は削除できない', function () {
    $order = SalesOrder::factory()->create();

    $this->delete(route('customers.destroy', $order->customer))
        ->assertSessionHasErrors('customer');

    $this->assertDatabaseHas('customers', ['id' => $order->customer_id]);
});

test('未ログインでは顧客一覧へアクセスできない', function () {
    auth()->logout();

    $this->get(route('customers.index'))->assertRedirect(route('login'));
});
