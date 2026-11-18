<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Permanently closes a user account. Shared by the scheduled command and by
 * the manual button in the admin panel, so both behave exactly the same.
 */
class AccountClosingService
{
    public const DELETED    = 'deleted';
    public const ANONYMIZED = 'anonymized';

    public function __construct(private readonly ImageService $images) {}

    /**
     * Deletes the account, or anonymizes it when it has financial history
     * (purchases must survive for accounting). Returns which one happened.
     */
    public function close(User $user): string
    {
        // Moderators and superadmins own catalog content and are handled by hand.
        if (! $user->isUser()) {
            throw new LogicException('Only plain user accounts can be closed here.');
        }

        if ($user->hasUploadedAvatar()) {
            $this->images->deleteImageAndThumb($user->avatar, 'public');
        }

        $hasFinancialHistory = $user->purchases()->exists();

        DB::transaction(function () use ($user, $hasFinancialHistory) {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();

            if (! $hasFinancialHistory) {
                // Downloads, social accounts and security rows go with it (cascade).
                $user->delete();

                return;
            }

            $user->socialAccounts()->delete();
            $user->productDownloads()->delete();
            $user->loginLogs()->delete();
            $user->security()->delete();

            // The row stays (purchases point to it) but loses every personal field.
            $user->forceFill([
                'name'                => 'Conta encerrada',
                'email'               => null,
                'email_verified_at'   => null,
                'pending_email'       => null,
                'pending_email_token' => null,
                'password'            => null,
                'remember_token'      => null,
                'cpf'                 => null,
                'birth_date'          => null,
                'phone'               => null,
                'avatar'              => null,
                'is_active'           => false,
            ])->save();
        });

        return $hasFinancialHistory ? self::ANONYMIZED : self::DELETED;
    }
}