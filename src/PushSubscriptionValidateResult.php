<?php

declare(strict_types=1);

namespace BbApp\PushService;

/**
 * Contains the validated object type and ID from a subscription request.
 */
class PushSubscriptionValidateResult
{
    public $object_type;
    public $object_id;

	/**
	 * Constructs a validation result with the given object type and ID.
	 *
	 * @param string $object_type
	 * @param int $object_id
	 */
    public function __construct(string $object_type, int $object_id)
    {
        $this->object_type = $object_type;
        $this->object_id = $object_id;
    }
}
