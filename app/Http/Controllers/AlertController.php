<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(): View
    {
        $alerts = Alert::where('user_id', auth()->id())
            ->with('device')
            ->latest()
            ->paginate(15);

        $unreadCount = Alert::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return view('alerts.index', compact('alerts', 'unreadCount'));
    }

    public function read(Alert $alert): RedirectResponse
    {
        if ($alert->user_id !== auth()->id()) {
            abort(404);
        }

        $alert->update(['read_at' => now()]);

        return back();
    }

    public function readAll(): RedirectResponse
    {
        Alert::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }
}
