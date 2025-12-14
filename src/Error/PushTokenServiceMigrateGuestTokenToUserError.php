<?php

declare(strict_types=1);

namespace BbApp\PushService\Error;

/**
 * Thrown when migrating a guest token to a user token fails.
 */
final class PushTokenServiceMigrateGuestTokenToUserError extends \Error
{
}
