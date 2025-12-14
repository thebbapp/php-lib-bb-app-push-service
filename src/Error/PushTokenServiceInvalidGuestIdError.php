<?php

declare(strict_types=1);

namespace BbApp\PushService\Error;

/**
 * Thrown when an invalid guest ID is provided to the push token service.
 */
final class PushTokenServiceInvalidGuestIdError extends \Error
{
}
