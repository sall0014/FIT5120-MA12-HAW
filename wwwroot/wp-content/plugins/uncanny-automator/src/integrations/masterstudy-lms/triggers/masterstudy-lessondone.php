<?php

namespace Uncanny_Automator;

/**
 * Class MASTER_LESSONDONE
 * @package Uncanny_Automator
 */
class MASTERSTUDY_LESSONDONE {

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
		$this->trigger_code = 'MSLMSLESSONDONE';
		$this->trigger_meta = 'MSLMSLESSON';
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

		$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, true, _x( 'Any course', 'MasterStudy LMS', 'uncanny-automator' ) );

		$course_relevant_tokens = [
			'MSLMSCOURSE'     => esc_attr__( 'Course title', 'uncanny-automator' ),
			'MSLMSCOURSE_ID'  => esc_attr__( 'Course ID', 'uncanny-automator' ),
			'MSLMSCOURSE_URL' => esc_attr__( 'Course URL', 'uncanny-automator' ),
		];
		$relevant_tokens        = [
			$this->trigger_meta          => esc_attr__( 'Lesson title', 'uncanny-automator' ),
			$this->trigger_meta . '_ID'  => esc_attr__( 'Lesson ID', 'uncanny-automator' ),
			$this->trigger_meta . '_URL' => esc_attr__( 'Lesson URL', 'uncanny-automator' ),
		];

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - MasterStudy LMS */
			'sentence'            => sprintf( esc_attr__( 'A user completes {{a lesson:%1$s}}', 'uncanny-automator' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - MasterStudy LMS */
			'select_option_name'  => esc_attr__( 'A user completes {{a lesson}}', 'uncanny-automator' ),
			'action'              => 'stm_lms_lesson_passed',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'lesson_completed' ),
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
							'endpoint'     => 'select_mslms_lesson_from_course_LESSONDONE',
						],
						$course_relevant_tokens
					),
					$uncanny_automator->helpers->recipe->field->select_field( $this->trigger_meta, esc_attr_x( 'Lesson', 'MasterStudy LMS', 'uncanny-automator' ), [], false, false, false, $relevant_tokens ),
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
	public function lesson_completed( $user_id, $lesson_id ) {

		global $uncanny_automator;

		$course_id = intval( $_GET['course'] );

		$args = [
			'code'    => $this->trigger_code,
			'meta'    => $this->trigger_meta,
			'post_id' => $lesson_id,
			'user_id' => $user_id,
		];

		$args = $uncanny_automator->maybe_add_trigger_entry( $args, false );

		if ( $args ) {
			foreach ( $args as $result ) {
				if ( true === $result['result'] ) {

					$course_id = absint($_GET['course']);

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
							'meta_key'       => $this->trigger_meta,
							'meta_value'     => $course_id,
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
