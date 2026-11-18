<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $purchases = $user->purchases()
            ->paid()
            ->with(['product', 'lastChapter'])
            ->latest('paid_at')
            ->get();

        // "Continue reading": the ebook read most recently.
        $reading = $purchases
            ->filter(fn ($purchase) => $purchase->last_read_at && $purchase->lastChapter && $purchase->product?->hasOnlineReader())
            ->sortByDesc('last_read_at')
            ->first();

        return view('site.account.index', [
            'user'          => $user,
            'hasPassword'   => filled($user->password),
            'deletionDate'  => $user->delete_requested_at?->copy()->addDays(User::DELETION_GRACE_DAYS),
            'purchases'     => $purchases->take(4),
            'libraryCount'  => $purchases->count(),
            'reading'       => $reading,
            'pendingCount'  => $user->purchases()->where('status', 'pending')->count(),
            // Something to buy when the library is still empty.
            'suggestions'   => $purchases->isEmpty()
                ? Product::published()->ordered()->limit(3)->get()
                : collect(),
        ]);
    }
}
