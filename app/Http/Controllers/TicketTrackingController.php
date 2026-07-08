<?php

namespace App\Http\Controllers;

use App\Models\DevelopmentItem;
use App\Models\DevelopmentReply;
use App\Services\DevelopmentTaskNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketTrackingController extends Controller
{
    public function show(string $token): View
    {
        $item = DevelopmentItem::where('public_token', $token)->with('replies')->firstOrFail();

        return view('ticket-tracking', ['item' => $item]);
    }

    public function reply(Request $request, string $token, DevelopmentTaskNotifier $notifier): RedirectResponse
    {
        $item = DevelopmentItem::where('public_token', $token)->firstOrFail();

        $data = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        DevelopmentReply::create([
            'development_item_id' => $item->id,
            'author' => 'cliente',
            'body' => $data['message'],
        ]);

        $notifier->replyAdded($item, 'cliente', $data['message']);

        return redirect()
            ->route('ticket.show', $token)
            ->with('reply_sent', true);
    }
}
