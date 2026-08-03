<?php

namespace App\Services;

use App\Mail\AdminTeamAlertMail;
use App\Models\DevelopmentItem;
use App\Support\AppSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DevelopmentTaskNotifier
{
    public function __construct(
        protected TelegramNotifier $telegram,
        protected AdminPushNotifier $push,
        protected AdminEmailNotifier $email,
    ) {}

    public function itemCreated(DevelopmentItem $item): void
    {
        $who = $this->authorLabel($item->author);
        $msg = $this->baseMessage('Nuova voce', $item);
        $body = "👤 <b>{$who}</b> ha creato: <b>{$this->telegram->escape($item->typeLabel())}</b>\n\n{$msg}";

        $this->deliver($body, 'task_created');

        if ($item->author === 'cliente') {
            $this->email->send(
                $item->title,
                "Nuovo ticket di assistenza.\n\n{$item->title}\n\n{$item->body}",
                url('/admin/progetto').'#task-board'
            );

            if ($item->client_email) {
                $url = url('/ticket/'.$item->public_token);
                $this->emailClient(
                    $item->client_email,
                    'Ticket ricevuto '.$item->ticketNumber(),
                    "Ciao,\n\nabbiamo ricevuto il tuo ticket di assistenza {$item->ticketNumber()}.\n\nPuoi seguirlo e rispondere qui:\n{$url}",
                    $url
                );
            }
        }
    }

    public function replyAdded(DevelopmentItem $item, string $author, string $replyBody): void
    {
        $who = $this->authorLabel($author);
        $msg = $this->baseMessage('Nuova risposta', $item);
        $body = "💬 <b>{$who}</b> ha risposto su: {$this->telegram->escape($item->title)}\n\n"
            .$this->telegram->escape($replyBody)."\n\n{$msg}";

        $this->deliver($body, 'task_reply');

        if ($author === 'cliente') {
            $this->email->send(
                'Risposta cliente: '.$item->title,
                "Il cliente ha risposto al ticket {$item->ticketNumber()}.\n\n{$replyBody}",
                url('/admin/progetto').'#task-board'
            );
        } elseif ($item->client_email) {
            $url = url('/ticket/'.$item->public_token);
            $this->emailClient(
                $item->client_email,
                'Risposta al tuo ticket '.$item->ticketNumber(),
                "Ciao,\n\nil team ha risposto al tuo ticket {$item->ticketNumber()}.\n\n{$replyBody}\n\nRispondi qui:\n{$url}",
                $url
            );
        }
    }

    protected function emailClient(string $to, string $title, string $body, string $url): void
    {
        if (! AppSettings::mailSmtpReady()) {
            Log::debug('Email cliente ticket: SMTP non configurato');

            return;
        }

        try {
            Mail::to($to)->send(new AdminTeamAlertMail($title, $body, $url));
        } catch (\Throwable $e) {
            Log::error('Email cliente ticket fallita', ['to' => $to, 'error' => $e->getMessage()]);
        }
    }

    public function statusChanged(DevelopmentItem $item, string $previousStatus): void
    {
        if ($item->status === DevelopmentItem::STATUS_IN_PROGRESS) {
            $body = "🔄 <b>In corso</b>\n".$this->baseMessage($item->title, $item);
            $this->deliver($body, 'task_progress');

            return;
        }

        if ($item->status === DevelopmentItem::STATUS_DONE) {
            $test = filled($item->test_instructions)
                ? "\n\n<b>Come testare:</b>\n".$this->telegram->escape($item->test_instructions)
                : '';
            $body = "✅ <b>Completata — pronta per test</b>\n".$this->baseMessage($item->title, $item).$test;
            $this->deliver($body, 'task_done');

            return;
        }

        if ($item->status === DevelopmentItem::STATUS_OPEN && $previousStatus !== DevelopmentItem::STATUS_OPEN) {
            $body = "📋 <b>Riaperta</b>\n".$this->baseMessage($item->title, $item);
            $this->deliver($body, 'task_reopen');
        }
    }

    protected function authorLabel(string $author): string
    {
        return match ($author) {
            'client' => 'Cliente',
            'cliente' => 'Cliente (ticket assistenza)',
            'team' => 'Team',
            default => 'Team',
        };
    }

    protected function baseMessage(string $headline, DevelopmentItem $item): string
    {
        $detail = $item->body ? "\n".$this->telegram->escape($item->body) : '';

        return "<b>{$this->telegram->escape($headline)}</b>\n"
            ."Tipo: {$this->telegram->escape($item->typeLabel())}\n"
            ."Stato: {$this->telegram->escape($item->statusLabel())}"
            .$detail;
    }

    protected function deliver(string $telegramHtml, string $pushType): void
    {
        $plain = strip_tags(str_replace(['<b>', '</b>'], ['', "\n"], $telegramHtml));
        $this->telegram->notifyAdmins($telegramHtml);
        $this->push->notifyAdmins($plain, $pushType, url('/admin/progetto'));
    }
}
