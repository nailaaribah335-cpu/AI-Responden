<?php

namespace App\Http\Controllers;

use App\Models\ConnectedStore;

/**
 * Dashboard utama seller — ringkasan statistik toko.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Kumpulkan data ringkasan untuk ditampilkan di dashboard
        $stats = [
            'total_stores'        => $user->stores()->count(),
            'active_stores'       => $user->stores()->where('is_active', true)->count(),
            'total_knowledge'     => $user->knowledgeBases()->count(),
            'active_knowledge'    => $user->knowledgeBases()->where('is_active', true)->count(),
            'total_conversations' => $user->conversations()->count(),
            'unread_messages'     => $user->conversations()->sum('unread_count'),
        ];

        $recentStores = $user->stores()->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'recentStores'));
    }
}
