<?php

declare(strict_types=1);

namespace App\MarketData\Adapters;

use RuntimeException;

/**
 * Exception thrown when the Quotex adapter encounters an error.
 * Distinct exception type allows the system to handle Quotex-specific
 * failures gracefully and display appropriate user-facing messages.
 */
final class QuotexAdapterException extends RuntimeException {}
