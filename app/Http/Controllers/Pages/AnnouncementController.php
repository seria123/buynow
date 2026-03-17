<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    // Display list of announcements for the logged-in user
    public function index()
    {
        $user = Auth::user();
        
        $announcements = Announcement::query()
            ->where('status', 'sent')
            ->where(function ($query) use ($user) {
                $query->where('target', 'all')
                    ->orWhere(function ($q) use ($user) {
                        $q->where('target', 'customer_group')
                            ->whereHas('customerGroup', function ($cq) use ($user) {
                                if ($user && $user->customer_group_id) {
                                    $cq->where('id', $user->customer_group_id);
                                }
                            });
                    })
                    ->orWhere(function ($q) use ($user) {
                        $q->where('target', 'individual')
                            ->where('user_id', $user?->id);
                    });
            })
            ->orderBy('sent_at', 'desc')
            ->get();

        return Inertia::render('Announcements/Index', [
            'announcements' => $announcements,
        ]);
    }

    // Display a single announcement
    public function show(Announcement $announcement)
    {
        // Check if user has access to this announcement
        $user = Auth::user();
        
        $hasAccess = $announcement->status === 'sent' && (
            $announcement->target === 'all' ||
            ($announcement->target === 'customer_group' && $user && $user->customer_group_id === $announcement->customer_group_id) ||
            ($announcement->target === 'individual' && $user && $user->id === $announcement->user_id)
        );

        if (!$hasAccess) {
            abort(403, 'You do not have access to this announcement.');
        }

        return Inertia::render('Announcements/Show', [
            'announcement' => $announcement,
        ]);
    }
}
