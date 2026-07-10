<?php

use App\Models\SalesOrder;
use App\Models\User;

test('受注CSVをダウンロードできる', function () {
    $this->actingAs(User::factory()->create());
    SalesOrder::factory()->count(3)->create();

    $response = $this->get(route('sales-orders.export'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv');
    expect($response->streamedContent())->toContain('受注番号');
});
