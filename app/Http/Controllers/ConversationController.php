<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $conversations = Conversation::where('user1_id', $userId)
            ->orWhere('user2_id', $userId)
            ->with('messages')
            ->get();

        return response()->json($conversations);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user2_id' => 'required|exists:users,id',
        ]);

        $conversation = Conversation::firstOrCreate([
            'user1_id' => $request->user()->id,
            'user2_id' => $request->user2_id,
        ]);

        return response()->json($conversation);
    }
}
