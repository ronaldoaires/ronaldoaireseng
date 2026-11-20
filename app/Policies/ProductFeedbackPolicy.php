<?php

namespace App\Policies;

use App\Models\ProductFeedback;
use App\Models\User;

class ProductFeedbackPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, ProductFeedback $productFeedback): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ProductFeedback $productFeedback): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, ProductFeedback $productFeedback): bool
    {
        return $user->isSuperAdmin();
    }
}
