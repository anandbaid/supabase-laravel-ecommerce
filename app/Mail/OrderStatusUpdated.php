<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $previousStatus)
    {
    }

    public function build(): self
    {
        return $this->subject("Your order {$this->order->order_number} is now " . ucfirst($this->order->status))
            ->markdown('emails.order-status-updated', [
                'order' => $this->order,
                'previousStatus' => $this->previousStatus,
            ]);
    }
}
