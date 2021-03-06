<?php

namespace Uncanny_Automator;

/**
 * Class WP_SUBMITCOMMENT
 * @package Uncanny_Automator
 */
class WP_SUBMITCOMMENT {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WP';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'WPSUBMITCOMMENT';
		$this->trigger_meta = 'WPPOSTCOMMENTS';
		if ( is_admin() ) {
			add_action( 'wp_loaded', array( $this, 'plugins_loaded' ), 99 );
		} else {
			$this->define_trigger();
		}
	}

	/**
	 *
	 */
	public function plugins_loaded() {
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;
		$all_post_types = $uncanny_automator->helpers->recipe->wp->options->all_post_types( null, 'WPPOSTTYPES', [
			'token'        => false,
			'is_ajax'      => true,
			'target_field' => $this->trigger_meta,
			'endpoint'     => 'select_all_post_from_SELECTEDPOSTTYPE',
		] );

		// now get regular post types.
		$args = [
			'public'   => true,
			'_builtin' => true,
		];

		$output        = 'object';
		$operator      = 'and';
		$options       = [];
		$options['- 1'] = __( 'Any post type', 'uncanny-automator' );
		$post_types    = get_post_types( $args, $output, $operator );
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				$options[ $post_type->name ] = esc_html( $post_type->labels->singular_name );
			}
		}
		$options                   = array_merge( $options, $all_post_types['options'] );
		$all_post_types['options'] = $options;
		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - WordPress */
			'sentence'            => sprintf(  esc_attr__( 'A user submits a comment on {{a post:%1$s}} {{a number of:%2$s}} time(s)', 'uncanny-automator' ), $this->trigger_meta, 'NUMTIMES' ),
			/* translators: Logged-in trigger - WordPress */
			'select_option_name'  => esc_attr__( 'A user submits a comment on {{a post}}', 'uncanny-automator' ),
			'action'              => 'comment_post',
			'priority'            => 90,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'submitted_comment' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->options->number_of_times(),
			],
			'options_group'       => [
				$this->trigger_meta => [
					$all_post_types,
					$uncanny_automator->helpers->recipe->field->select_field(
						$this->trigger_meta,
						__( 'Post', 'uncanny-automator-pro' ),
						[],
						null,
						false,
						false,
						[
							$this->trigger_meta          => esc_attr__( 'Page title', 'uncanny-automator' ),
							$this->trigger_meta . '_ID'  => esc_attr__( 'Page ID', 'uncanny-automator' ),
							$this->trigger_meta . '_URL' => esc_attr__( 'Page URL', 'uncanny-automator' ),
						]
					),
				],
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param int $comment_id The comment ID.
	 * @param int|string $comment_approved 1 if the comment is approved, 0 if not, 'spam' if spam.
	 * @param array $commentdata Comment data.
	 */
	public function submitted_comment( $comment_id, $comment_approved, $commentdata ) {

		global $uncanny_automator;

		$user_id   = get_current_user_id();
		$post_type = get_post_type( $commentdata['comment_post_ID'] );
		// We need backword compatibility along with new change
		$recipes    = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$conditions = $this->match_condition( $commentdata['comment_post_ID'], $post_type , $recipes, $this->trigger_meta, $this->trigger_code );

		if ( ! $conditions ) {
			return;
		}
		$post_type = get_post_type_object( $post_type );
		if ( ! empty( $conditions ) ) {
			foreach ( $conditions['recipe_ids'] as $recipe_id ) {
				if ( ! $uncanny_automator->is_recipe_completed( $recipe_id, $user_id ) ) {
					$args = [
						'code'            => $this->trigger_code,
						'meta'            => $this->trigger_meta,
						'recipe_to_match' => $recipe_id,
						'post_id'         => $commentdata['comment_post_ID'],
						'user_id'         => $user_id,
					];

					$arr = $uncanny_automator->maybe_add_trigger_entry( $args, false );
					if ( $arr ) {
						foreach ( $arr as $result ) {
							if ( true === $result['result'] ) {
								$trigger_meta = [
									'user_id'        => $user_id,
									'trigger_id'     => $result['args']['trigger_id'],
									'trigger_log_id' => $result['args']['get_trigger_id'],
									'run_number'     => $result['args']['run_number'],
								];

								// Post
								$trigger_meta['meta_key']   = 'WPPOSTCOMMENTS';
								$trigger_meta['meta_value'] = $commentdata['comment_post_ID'];
								$uncanny_automator->insert_trigger_meta( $trigger_meta );

								// Post Type Token
								$trigger_meta['meta_key']   = 'WPPOSTTYPES';
								$trigger_meta['meta_value'] = maybe_serialize( $post_type->labels->singular_name );
								$uncanny_automator->insert_trigger_meta( $trigger_meta );

								$uncanny_automator->maybe_trigger_complete( $result['args'] );
							}
						}
					}
				}
			}
		}

	}

	/**
	 * Matching Form id because its not an integer.
	 *
	 * @param array $post_id .
	 * @param array $recipes .
	 * @param string $trigger_meta .
	 * @param string $trigger_code .
	 *
	 * @return array|bool
	 */
	public function match_condition( $post_id, $post_type, $recipes = null, $trigger_meta = null, $trigger_code = null ) {

		if ( null === $recipes ) {
			return false;
		}

		$recipe_ids      = [];
		$match_post_id   = $post_id;
		$match_post_type = $post_type;
		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if( ! key_exists('WPPOSTTYPES', $trigger['meta'])){
					if ( key_exists( $trigger_meta, $trigger['meta'] ) && ( (string) $trigger['meta'][ $trigger_meta ] === (string) $match_post_id || '-1' === (string) $trigger['meta'][ $trigger_meta ] ) ) {
						$recipe_ids[ $recipe['ID'] ] = $recipe['ID'];
					}
				} else {
					if ( key_exists( $trigger_meta, $trigger['meta'] )
					     && ( (string) $trigger['meta'][ $trigger_meta ] === (string) $match_post_id || '-1' === (string) $trigger['meta'][ $trigger_meta ] )
					     && ( (string) $trigger['meta']['WPPOSTTYPES'] === (string) $match_post_type || '-1' === (string) $trigger['meta']['WPPOSTTYPES'] )
					) {
						$recipe_ids[ $recipe['ID'] ] = $recipe['ID'];
					}
				}
			}
		}

		if ( ! empty( $recipe_ids ) ) {
			return [ 'recipe_ids' => $recipe_ids, 'result' => true ];
		}

		return false;
	}
}
