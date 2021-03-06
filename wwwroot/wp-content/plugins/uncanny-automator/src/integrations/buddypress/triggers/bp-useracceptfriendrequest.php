<?php

namespace Uncanny_Automator;

/**
 * Class BP_USERACCEPTFRIENDREQUEST
 * @package Uncanny_Automator
 */
class BP_USERACCEPTFRIENDREQUEST {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'BP';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'BPUSERACCEPTFRIENDREQUEST';
		$this->trigger_meta = 'BPUSERS';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - BuddyPress */
			'sentence'            => esc_attr__('A user accepts a friendship request', 'uncanny-automator' ),
			/* translators: Logged-in trigger - BuddyPress */
			'select_option_name'  => esc_attr__('A user accepts a friendship request', 'uncanny-automator' ),
			'action'              => 'friends_friendship_accepted',
			'priority'            => 10,
			'accepted_args'       => 4,
			'validation_function' => array( $this, 'bp_friends_friendship_accepted' ),
			'options'             => [],
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 *  Validation function when the trigger action is hit
	 *
	 * @param $id
	 * @param $initiator_user_id
	 * @param $friend_user_id
	 * @param $friendship
	 */
	public function bp_friends_friendship_accepted( $id, $initiator_user_id, $friend_user_id, $friendship ) {

		global $uncanny_automator;

		$args = [
			'code'           => $this->trigger_code,
			'meta'           => $this->trigger_meta,
			'user_id'        => $friend_user_id,
			'ignore_post_id' => true,
			'is_signed_in'   => true,
		];

		$uncanny_automator->maybe_add_trigger_entry( $args );
	}
}
