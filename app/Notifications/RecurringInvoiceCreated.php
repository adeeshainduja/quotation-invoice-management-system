<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RecurringInvoiceCreated extends Notification
{
    use Queueable;

    public function __construct(
        public object $invoice
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Scheduled Invoice Created')
            ->greeting('Hello ' . $notifiable->name)
            ->line(
                'A scheduled invoice has been created automatically.'
            )
            ->line(
                'Invoice: ' . $this->invoice->invoice_number
            )
            ->line(
                'Amount: ' .
                number_format($this->invoice->grand_total, 2)
            )
            ->action(
                'View Invoices',
                route('invoices.index', [
                    'company_id' => $this->invoice->company_id
                ])
            );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Scheduled Invoice Created',
            'message' =>
                $this->invoice->invoice_number .
                ' was created automatically.',

            'invoice_id' => $this->invoice->id,
            'company_id' => $this->invoice->company_id,
        ];
    }
}