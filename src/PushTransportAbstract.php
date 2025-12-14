<?php

declare(strict_types=1);

namespace BbApp\PushService;

use BbApp\ContentSource\ContentSourceAbstract;
use BbApp\Result\Result;

/**
 * Base class for push notification transport implementations.
 *
 * @var string $id
 * @var bool $message_envelope_subtitle
 * @var ContentSourceAbstract $content_source
 */
abstract class PushTransportAbstract
{
	/**
	 * @var array<PushTransportAbstract> $registry
	 */
	public static $registry = [];

	/**
	 * Registers a push transport in the global registry.
	 *
	 * @param PushTransportAbstract $push_transport
	 * @return void
	 */
	public static function register(PushTransportAbstract $push_transport): void
	{
		if (static::locate($push_transport->id)) {
			return;
		}

		static::$registry[] = $push_transport;
	}

	/**
	 * Locates a registered push transport by its ID.
	 *
	 * @param string $id
	 * @return PushTransportAbstract|null
	 */
	public static function locate(string $id): ?PushTransportAbstract {
		foreach (static::$registry as $push_transport) {
			if ($push_transport->id === $id) {
				return $push_transport;
			}
		}

		return null;
	}

	/**
	 * Locates registered push transport IDs.
	 *
	 * @return array<string>
	 */
	public static function get_ids(): array {
		return array_map(
			function (PushTransportAbstract $push_transport) {
				return $push_transport->id;
			},

			static::$registry
		);
	}

	public $id;
	public $message_envelope_subtitle;
	public $content_source;

	protected $options;

	/**
	 * Constructs a push transport with the given content source and options.
	 *
	 * @param ContentSourceAbstract $content_source
	 * @param PushTransportOptions $options
	 */
	public function __construct(
		ContentSourceAbstract $content_source,
		PushTransportOptions $options
	) {
		$this->content_source = $content_source;
		$this->options = $options;
	}

	/**
	 * Checks if the token's user or guest can view the specified content.
	 *
	 * @param array $token
	 * @param mixed $content_type
	 * @param mixed $content_id
	 * @return bool
	 */
	public function token_user_content_can_view(
		array $token,
		$content_type,
		$content_id
	): bool {
		if (
			!empty($token['user_id']) &&
			$token['user_id'] > 0 &&
			$this->content_source->user_can((int) $token['user_id'], 'view', $content_type, $content_id) === false
		) {
			return false;
		} else if (
			!empty($token['guest_id']) &&
			$this->content_source->user_can(0, 'view', $content_type, $content_id) === false
		) {
			return false;
		}

		return true;
	}

	/**
	 * Sends a push notification to the specified tokens.
	 *
	 * @param array $tokens
	 * @param string $title
	 * @param string $body
	 * @param string|null $subtitle
	 * @param string|null $imageUrl
	 * @param string|null $url
	 * @param int|null $badge
	 * @return array
	 */
	abstract public function send(
		array $tokens,
		string $title,
		string $body,
		?string $subtitle = null,
		?string $imageUrl = null,
		?string $url = null,
		?int $badge = null
	): array;

	/**
	 * Handles a scheduled notification event for the given tokens.
	 *
	 * @param array $tokens
	 * @param array $envelope
	 * @param string $content_type
	 * @param int $content_id
	 * @return array
	 */
	abstract public function handle_scheduled_event(
		array $tokens,
		array $envelope,
		string $content_type,
		int $content_id
	): array;

	/**
	 * Validates a push token format.
	 *
	 * @param string $token
	 * @return Result
	 */
	abstract public function validate_push_token(string $token): Result;

	/**
	 * Encodes a push token for storage.
	 *
	 * @param string $token
	 * @return string
	 */
	abstract public function encode_push_token(string $token): string;

	/**
	 * Decodes a push token from storage.
	 *
	 * @param string $encoded_token
	 * @return string
	 */
	abstract public function decode_push_token(string $encoded_token): string;
}
