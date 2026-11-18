<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Payment history: every attempt, paid or not.
 */
class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $purchases = $request->user()
            ->purchases()
            ->with('product')
            ->latest('updated_at')
            ->paginate(15);

        return view('site.account.purchases', ['purchases' => $purchases]);
    }
}
