<?php

namespace App\Services;

use RuntimeException;

/** A checkout problem with a message that is safe to show the customer. */
class OrderPlacementException extends RuntimeException
{
    /**
     * @param  int  $status  HTTP status for API responses: 422 for problems the
     *                       customer can fix, 500 when saving the order failed.
     */
    public function __construct(
        string $message,
        public readonly bool $cartIsEmpty = false,
        public readonly int $status = 422,
    ) {
        parent::__construct($message);
    }
}
