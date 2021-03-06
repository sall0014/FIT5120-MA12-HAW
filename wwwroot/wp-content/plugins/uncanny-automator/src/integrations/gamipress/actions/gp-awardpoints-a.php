<?php

namespace Uncanny_Automator;

/**
 * Class GP_AWARDPOINTS_A
 * @package Uncanny_Automator
 */
class GP_AWARDPOINTS_A {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'GP';

	private $action_code;
	private $action_meta;
	private $quiz_list;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'GPAWARDPOINTS';
		$this->action_meta = 'GPPOINTS';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = [
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link(),
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - GamiPress */
			'sentence'           => sprintf(  esc_attr__( 'Award {{a number:%1$s}} {{of a specific type of:%2$s}} points to the user', 'uncanny-automator' ), 'GPPOINTVALUE', $this->action_meta ),
			/* translators: Action - GamiPress */
			'select_option_name' =>  esc_attr__( 'Award {{points}} to the user', 'uncanny-automator' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'award_points' ],
			'options'            => [],
			'options_group'      => [
				$this->action_meta => [
					$uncanny_automator->helpers->recipe->gamipress->options->list_gp_points_types(  esc_attr__( 'Point type', 'uncanny-automator' ), $this->action_meta, [
						'token'   => false,
						'is_ajax' => false,
					] ),
				],
				'GPPOINTVALUE'     => [
					[
						'input_type' => 'int',

						'option_code' => 'GPPOINTVALUE',
						'label'       =>  esc_attr__( 'Points', 'uncanny-automator' ),

						'supports_tokens' => true,
						'required'        => true,
					],
				],
			],
		];

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function award_points( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$points_type = $action_data['meta'][ $this->action_meta ];
		$points      = $uncanny_automator->parse->text( $action_data['meta']['GPPOINTVALUE'], $recipe_id, $user_id, $args );
		gamipress_award_points_to_user( absint( $user_id ), absint( $points ), $points_type );

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}

}
