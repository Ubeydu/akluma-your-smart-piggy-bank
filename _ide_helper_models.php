<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $created_at
 * @property string $updated_at
 * @property int $user_id
 * @property string $name
 * @property float $price
 * @property float|null $starting_amount
 * @property float $target_amount
 * @property float|null $extra_savings
 * @property float $total_savings
 * @property string|null $link
 * @property string|null $details
 * @property string $chosen_strategy
 * @property string $selected_frequency
 * @property string $preview_image
 * @property string $currency
 * @property string $status
 * @property string|null $preview_title
 * @property string|null $preview_description
 * @property string|null $preview_url
 * @property float $final_total
 * @property-read float $remaining_amount
 * @property-read float $actual_final_total_saved
 * @property-read float $current_balance
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScheduledSaving> $scheduledSavings
 * @property-read int|null $scheduled_savings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PiggyBankTransaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\PiggyBankFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereChosenStrategy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereExtraSavings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereFinalTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank wherePreviewDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank wherePreviewImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank wherePreviewTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank wherePreviewUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereSelectedFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereStartingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereTargetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereTotalSavings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBank whereUserId($value)
 */
	class PiggyBank extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $piggy_bank_id
 * @property int $user_id
 * @property string $type
 * @property string $amount
 * @property string|null $note
 * @property string|null $scheduled_for
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PiggyBank $piggyBank
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBankTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBankTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBankTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBankTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBankTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBankTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBankTransaction whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBankTransaction wherePiggyBankId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBankTransaction whereScheduledFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBankTransaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBankTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PiggyBankTransaction whereUserId($value)
 */
	class PiggyBankTransaction extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $piggy_bank_id
 * @property int $saving_number
 * @property float $amount
 * @property string $status
 * @property Carbon $saving_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read PiggyBank $piggyBank
 * @property string|null $notification_statuses
 * @property string|null $notification_attempts
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledSaving newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledSaving newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledSaving query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledSaving whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledSaving whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledSaving whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledSaving whereNotificationAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledSaving whereNotificationStatuses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledSaving wherePiggyBankId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledSaving whereSavingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledSaving whereSavingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledSaving whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledSaving whereUpdatedAt($value)
 */
	class ScheduledSaving extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property mixed $language
 * @property string $currency
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $timezone
 * @property array<array-key, mixed>|null $notification_preferences
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $accepted_terms_at
 * @property \Illuminate\Support\Carbon|null $accepted_privacy_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PiggyBank> $piggyBanks
 * @property-read int|null $piggy_banks_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAcceptedPrivacyAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAcceptedTermsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNotificationPreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

