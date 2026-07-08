<?php

namespace App\Http\Controllers;

use App\Models\DevelopmentItem;
use App\Services\DevelopmentTaskNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function show(): View
    {
        return view('assistenza');
    }

    public function store(Request $request, DevelopmentTaskNotifier $notifier): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:120',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $contact = trim(($data['name'] ?? '').' <'.$data['email'].'>');
        $body = "Da: {$contact}\n\n{$data['message']}";

        $item = DevelopmentItem::create([
            'type' => DevelopmentItem::TYPE_SERENELLA_REQUEST,
            'status' => DevelopmentItem::STATUS_OPEN,
            'title' => 'Ticket assistenza: '.$data['subject'],
            'body' => $body,
            'author' => 'cliente',
            'client_name' => $data['name'] ?? null,
            'client_email' => $data['email'],
            'public_token' => Str::random(32),
        ]);

        $notifier->itemCreated($item);

        return redirect()
            ->route('ticket.show', $item->public_token)
            ->with('ticket_sent', true);
    }
}
