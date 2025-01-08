<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Message;

class MessageController extends Controller
{
    // Send a message or file
    public function sendMessage(Request $request)
    {
        // Validate the request
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'encrypted_message' => 'nullable|string',
            'file' => 'nullable|file|max:2048', // Optional file, max 2MB
        ]);

        // Create a new message record
        $message = new Message();
        $message->sender_id = Auth::id();
        $message->recipient_id = $request->recipient_id;
        // $message->file_name = $request->$file->getClientOriginalName(); // Save the file name if a file is attached
        $message->encrypted_message = $request->encrypted_message; // Set the message text if provided

        // Handle file upload if a file is attached
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('uploads', 'public');
            $message->file_path = $filePath;
            $message->file_name = $file->getClientOriginalName(); // Safely retrieve the file name
        }
        

        // Save the message to the database
        $message->save();

        // Return success response
        return response()->json(['message' => 'Message/File sent successfully!']);
    }


    // Retrieve messages for the logged-in user
    public function getMessages($recipientId)
    {
        $messages = Message::where(function ($query) use ($recipientId) {
            $query->where('recipient_id', Auth::id())
                  ->where('sender_id', $recipientId);
        })
        ->orWhere(function ($query) use ($recipientId) {
            $query->where('sender_id', Auth::id())
                  ->where('recipient_id', $recipientId);
        })
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($message) {
            $message->sent = $message->sender_id === Auth::id(); // Add `sent` flag
            $message->file_url = $message->file_path ? asset('storage/' . $message->file_path) : null;
            return $message;
        });
    
    return response()->json($messages);
    
    }
}




