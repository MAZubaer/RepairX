<?php

namespace App\Mail;

use App\Models\ServiceRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceInProgressMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ServiceRecord $serviceRecord;

    /**
     * Create a new message instance.
     */
    public function __construct(ServiceRecord $serviceRecord)
    {
        $this->serviceRecord = $serviceRecord;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $model = $this->serviceRecord->phone_model ?: 'Your device';

        return new Envelope(
            subject: "{$model} repair is in progress",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->serviceRecord->loadMissing([
            'customer.user:id,name',
            'shop.user:id,name',
        ]);

        return new Content(
            view: 'emails.service-in-progress',
            with: [
                'serviceRecord' => $this->serviceRecord,
                'customerName' => $this->serviceRecord->customer?->user?->name ?? 'Customer',
                'shopName' => $this->serviceRecord->shop?->user?->name ?? 'the shop',
            ],
        );
    }
}
