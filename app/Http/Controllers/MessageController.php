<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string',
        ]);

        $conversation = Conversation::find($request->conversation_id);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $request->user()->id,
            'receiver_id' => ($conversation->user1_id == $request->user()->id)
                ? $conversation->user2_id
                : $conversation->user1_id,
            'message' => $request->message,
        ]);

        return response()->json($message);
    }

    public function getMessages($conversationId)
    {
        $messages = Message::where('conversation_id', $conversationId)->get();
        return response()->json($messages);
    }

    public function markAsRead($messageId)
    {
        $message = Message::findOrFail($messageId);
        $message->update(['is_read' => true]);

        return response()->json(['message' => 'Message marked as read']);
    }
}
