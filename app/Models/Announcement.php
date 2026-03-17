<?php

namespace App\Models;

use App\Notifications\AnnouncementNotification;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Announcement extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'content',
        'type',
        'target',
        'customer_group_id',
        'user_id',
        'created_by',
        'scheduled_at',
        'sent_at',
        'status',
        'recipients_count',
        'sent_count',
        'opened_count',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'recipients_count' => 'integer',
        'sent_count' => 'integer',
        'opened_count' => 'integer',
    ];

    /**
     * Get the user that created the announcement
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the customer group targeted by this announcement
     */
    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    /**
     * Get the individual user targeted by this announcement
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the users who received this announcement
     */
    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_recipients')
            ->withPivot('sent_at', 'opened_at')
            ->withTimestamps();
    }

    /**
     * Scope to get only draft announcements
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to get only scheduled announcements
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope to get only sent announcements
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Get the target users for this announcement based on target type
     */
    public function getTargetUsers(): Collection
    {
        return match ($this->target) {
            'all' => User::whereHas('orders')->orWhereNotNull('email_verified_at')->get(),
            'customer_group' => $this->customerGroup_id 
                ? User::where('customer_group_id', $this->customerGroup_id)->get() 
                : collect(),
            'individual' => $this->user_id 
                ? User::where('id', $this->user_id)->get() 
                : collect(),
            default => collect(),
        };
    }

    /**
     * Get eligible recipients (filters by marketing opt-in for promotional/Newsletter types)
     */
    public function getEligibleRecipients(): Collection
    {
        $users = $this->getTargetUsers();

        // For promotional and newsletter types, filter by marketing opt-in
        if (in_array($this->type, ['promotional', 'newsletter'])) {
            $users = $users->filter(fn($user) => $user->marketing_opt_in);
        }

        return $users;
    }

    /**
     * Send the announcement to all eligible recipients
     */
    public function send(): int
    {
        $recipients = $this->getEligibleRecipients();
        $sentCount = 0;

        foreach ($recipients as $user) {
            try {
                $user->notify(new AnnouncementNotification($this));
                $this->recipients()->attach($user->id, ['sent_at' => now()]);
                $sentCount++;
            } catch (\Exception $e) {
                \Log::error("Failed to send announcement {$this->id} to user {$user->id}: " . $e->getMessage());
            }
        }

        $this->update([
            'sent_at' => now(),
            'status' => 'sent',
            'sent_count' => $sentCount,
        ]);

        return $sentCount;
    }

    /**
     * Schedule the announcement for later sending
     */
    public function schedule(\DateTime $scheduledAt): void
    {
        $this->update([
            'scheduled_at' => $scheduledAt,
            'status' => 'scheduled',
        ]);
    }

    /**
     * Cancel a scheduled announcement
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
        ]);
    }

    /**
     * Get the type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'general' => 'General',
            'promotional' => 'Promotional',
            'order' => 'Order Related',
            'account' => 'Account',
            'newsletter' => 'Newsletter',
            default => 'Unknown',
        };
    }

    /**
     * Get the target label
     */
    public function getTargetLabelAttribute(): string
    {
        return match ($this->target) {
            'all' => 'All Customers',
            'customer_group' => $this->customerGroup?->name ?? 'Customer Group',
            'individual' => $this->user?->name ?? 'Individual',
            default => 'Unknown',
        };
    }
}
