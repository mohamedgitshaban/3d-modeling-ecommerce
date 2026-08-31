<?php

use App\Http\Controllers\Webhooks\PaymobWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/paymob/transaction', [PaymobWebhookController::class, 'handleTransaction'])
    ->name('webhooks.paymob.transaction')
    ->middleware('throttle:120,1');
