<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BroadcastMessage;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function index()
    {
        $broadcasts = BroadcastMessage::with('creator')->latest()->paginate(10);
        return view('admin.broadcast.index', compact('broadcasts'));
    }

    public function create()
    {
        return view('admin.broadcast.form', ['broadcast' => new BroadcastMessage()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $broadcast = BroadcastMessage::create([
            'title' => $request->title,
            'message' => $request->message,
            'created_by' => auth()->id(),
        ]);

        $petani = User::where('role', 'petani')->where('status', 'active')->get();
        foreach ($petani as $user) {
            NotificationService::notifyBroadcast($user, $broadcast->title, $broadcast->message);
        }

        return redirect()->route('admin.broadcast.index')->with('success', "Broadcast berhasil dikirim ke {$petani->count()} petani");
    }
}
