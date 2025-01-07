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
        $message->encrypted_message = $request->encrypted_message; // Set the message text if provided

        // Handle file upload if a file is attached
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('uploads', 'public'); // Save file in the "storage/app/public/uploads" directory
            $message->file_path = $filePath; // Save the file path in the database
        }

        // Save the message to the database
        $message->save();

        // Return success response
        return response()->json(['message' => 'Message/File sent successfully!']);
    }


    // Retrieve messages for the logged-in user
    public function getMessages()
    {
        $messages = Message::where('recipient_id', Auth::id())
            ->orWhere('sender_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($messages);
    }
}
