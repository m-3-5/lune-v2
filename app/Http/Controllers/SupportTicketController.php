<?php

namespace App\Http\Controllers;

use App\Models\DevelopmentItem;
use App\Services\DevelopmentTaskNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'email' => 'nullable|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $contact = trim(($data['name'] ?? '').(filled($data['email'] ?? null) ? ' <'.$data['email'].'>' : ''));
        $body = ($contact !== '' ? "Da: {$contact}\n\n" : '')."{$data['message']}";

        $item = DevelopmentItem::create([
            'type' => DevelopmentItem::TYPE_SERENELLA_REQUEST,
            'status' => DevelopmentItem::STATUS_OPEN,
            'title' => 'Ticket assistenza: '.$data['subject'],
            'body' => $body,
            'author' => 'cliente',
        ]);

        $notifier->itemCreated($item);

        return redirect()
            ->route('assistenza')
            ->with('ticket_sent', true);
    }
}
