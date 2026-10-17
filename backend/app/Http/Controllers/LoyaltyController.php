<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyTransaction;

class LoyaltyController extends Controller
{
    public function index()
    {
        $transactions = LoyaltyTransaction::where('user_id', request()->user()->id)
            ->orderByDesc('id')
            ->paginate(15);
        $balance = app(\App\Services\LoyaltyService::class)->balance(request()->user()->id);

        return view('loyalty.index', compact('transactions', 'balance'));
    }
}
