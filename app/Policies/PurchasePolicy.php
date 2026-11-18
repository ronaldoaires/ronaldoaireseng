<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy
{
    // Sales are money: superadmin only. Rows are never deleted from the panel.

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Purchase $record): bool
    {
        return $user->isSuperAdmin();
    }

    // Manual access grants (header action "Liberar acesso").
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Purchase $record): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Purchase $record): bool
    {
        return false;
    }
}
