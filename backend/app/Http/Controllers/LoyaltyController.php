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
        $balance = (int) ($transactions->first()?->balance_after ?? LoyaltyTransaction::where('user_id', request()->user()->id)->latest('id')->value('balance_after'));

        return view('loyalty.index', compact('transactions', 'balance'));
    }
}
