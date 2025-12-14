<?php

declare(strict_types=1);

namespace BbApp\PushService;

/**
 * Configuration options for validating a push subscription.
 */
class PushSubscriptionValidateOptions
{
    public $content_type;
    public $content_id;

	/**
	 * Constructs validation options with the given content type and ID.
	 *
	 * @param string $content_type
	 * @param int $content_id
	 */
    public function __construct(string $content_type, int $content_id)
    {
        $this->content_type = $content_type;
        $this->content_id = $content_id;
    }
}
