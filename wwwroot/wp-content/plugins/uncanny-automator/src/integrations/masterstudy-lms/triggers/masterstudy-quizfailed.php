<?php

namespace Uncanny_Automator;

/**
 * Class MASTERSTUDY_QUIZFAILED
 * @package Uncanny_Automator
 */
class MASTERSTUDY_QUIZFAILED {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'MSLMS';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'MSLMSQUIZFAILED';
		$this->trigger_meta = 'MSLMSQUIZ';
		$this->define_trigger();

	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$args = [
			'post_type'      => 'stm-courses',
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];

		$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, true, __( 'Any course', 'uncanny-automator' ) );

		$course_relevant_tokens = [
			'MSLMSCOURSE'     => esc_attr__( 'Course title', 'uncanny-automator' ),
			'MSLMSCOURSE_ID'  => esc_attr__( 'Course ID', 'uncanny-automator' ),
			'MSLMSCOURSE_URL' => esc_attr__( 'Course URL', 'uncanny-automator' ),
		];
		$relevant_tokens        = [
			$this->trigger_meta            => esc_attr__( 'Quiz title', 'uncanny-automator' ),
			$this->trigger_meta . '_ID'    => esc_attr__( 'Quiz ID', 'uncanny-automator' ),
			$this->trigger_meta . '_URL'   => esc_attr__( 'Quiz URL', 'uncanny-automator' ),
			$this->trigger_meta . '_SCORE' => esc_attr__( 'Quiz score', 'uncanny-automator' ),
		];

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - MasterStudy LMS */
			'sentence'            => sprintf( esc_attr__( 'A user fails {{a quiz:%1$s}}', 'uncanny-automator' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - MasterStudy LMS */
			'select_option_name'  => esc_attr__( 'A user fails {{a quiz}}', 'uncanny-automator' ),
			'action'              => 'stm_lms_quiz_failed',
			'priority'            => 10,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'quiz_failed' ),
			'options'             => [],
			'options_group'       => [
				$this->trigger_meta => [
					$uncanny_automator->helpers->recipe->field->select_field_ajax(
						'MSLMSCOURSE',
						esc_attr_x( 'Course', 'MasterStudy LMS', 'uncanny-automator' ),
						$options,
						'',
						'',
						false,
						true,
						[
							'target_field' => $this->trigger_meta,
							'endpoint'     => 'select_mslms_quiz_from_course_QUIZ',
						],
						$course_relevant_tokens
					),
					$uncanny_automator->helpers->recipe->field->select_field( $this->trigger_meta, esc_attr__( 'Quiz', 'uncanny-automator' ), [], false, false, false, $relevant_tokens ),
				],
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $data
	 */
	public function quiz_failed( $user_id, $quiz_id, $user_quiz_progress ) {

		global $uncanny_automator;

		$args = [
			'code'    => $this->trigger_code,
			'meta'    => $this->trigger_meta,
			'post_id' => $quiz_id,
			'user_id' => $user_id,
		];

		$args = $uncanny_automator->maybe_add_trigger_entry( $args, false );

		if ( $args ) {
			foreach ( $args as $result ) {
				if ( true === $result['result'] ) {

					$source    = ( ! empty( $_POST['source'] ) ) ? intval( $_POST['source'] ) : '';
					$course_id = ( ! empty( $_POST['course_id'] ) ) ? intval( $_POST['course_id'] ) : '';
					$course_id = apply_filters( 'user_answers__course_id', $course_id, $source );

					$uncanny_automator->insert_trigger_meta(
						[
							'user_id'        => $user_id,
							'trigger_id'     => $result['args']['trigger_id'],
							'meta_key'       => 'MSLMSCOURSE',
							'meta_value'     => $course_id,
							'trigger_log_id' => $result['args']['get_trigger_id'],
							'run_number'     => $result['args']['run_number'],
						]
					);

					$uncanny_automator->insert_trigger_meta(
						[
							'user_id'        => $user_id,
							'trigger_id'     => $result['args']['trigger_id'],
							'meta_key'       => $this->trigger_meta . '_SCORE',
							'meta_value'     => $user_quiz_progress . '%',
							'trigger_log_id' => $result['args']['get_trigger_id'],
							'run_number'     => $result['args']['run_number'],
						]
					);

					$uncanny_automator->insert_trigger_meta(
						[
							'user_id'        => $user_id,
							'trigger_id'     => $result['args']['trigger_id'],
							'meta_key'       => $this->trigger_meta,
							'meta_value'     => $quiz_id,
							'trigger_log_id' => $result['args']['get_trigger_id'],
							'run_number'     => $result['args']['run_number'],
						]
					);
					$uncanny_automator->maybe_trigger_complete( $result['args'] );
				}
			}
		}
	}
}
