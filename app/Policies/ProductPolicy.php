<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    // Any admin edits the catalog; only the superadmin removes products.

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Product $record): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Product $record): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Product $record): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Product $record): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Product $record): bool
    {
        return $user->isSuperAdmin();
    }
}
