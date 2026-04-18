<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function index(Request $request): View
    {
        $messages = ContactMessage::query()
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('inbox.index', compact('messages'));
    }

    public function show(ContactMessage $message): View
    {
        return view('inbox.show', compact('message'));
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()
            ->route('inbox.index')
            ->with('success', 'Message deleted.');
    }
}

