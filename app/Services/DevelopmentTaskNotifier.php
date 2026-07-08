<?php

namespace App\Services;

use App\Models\DevelopmentItem;

class DevelopmentTaskNotifier
{
    public function __construct(
        protected TelegramNotifier $telegram,
        protected AdminPushNotifier $push,
    ) {}

    public function itemCreated(DevelopmentItem $item): void
    {
        $who = $this->authorLabel($item->author);
        $msg = $this->baseMessage('Nuova voce', $item);
        $body = "👤 <b>{$who}</b> ha creato: <b>{$this->telegram->escape($item->typeLabel())}</b>\n\n{$msg}";

        $this->deliver($body, 'task_created');
    }

    public function replyAdded(DevelopmentItem $item, string $author, string $replyBody): void
    {
        $who = $this->authorLabel($author);
        $msg = $this->baseMessage('Nuova risposta', $item);
        $body = "💬 <b>{$who}</b> ha risposto su: {$this->telegram->escape($item->title)}\n\n"
            .$this->telegram->escape($replyBody)."\n\n{$msg}";

        $this->deliver($body, 'task_reply');
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
            'serenella' => 'Serenella',
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
