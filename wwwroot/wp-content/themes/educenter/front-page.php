<?php
/**
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Educenter
 */
get_header(); ?>


<?php
	
	/**
	 * Load Front Page
	 */
	do_action( 'educenter_frontpage' );

	$educenter_frontpage = get_theme_mod( 'educenter_set_frontpage' ,false);

	if( $educenter_frontpage == 1 ){
	
		/**
		 * Main Banner/Slider Section
		 *
		 * @see educenter_banner_section() -- 5
		 *
		*/
		do_action( 'educenter_action_front_page' );

		
		$section_order = get_theme_mod( 'educenter_homepage_section_order_list' );

		if( empty( $section_order ) ) {

		    $educenter_home_section_order = array(
		                            '0'  => 'features',
		                            '1'  => 'aboutus',
		                            '2'  => 'cta',
		                            '3'  => 'services',
		                            '4'  => 'counter',                                    
		                            '5'  => 'courses',
		                            '6'  => 'ourblog',
		                            '7'  => 'ourteam',
		                            '8'  => 'gallery',
		                            '9'  => 'testimonial'
		                        );
		} else {
		    $save_section_order = explode( ',' , $section_order);

		    $section_order_pop = array_pop( $save_section_order );

		    $educenter_home_section_order = $save_section_order;
		}

		foreach ( $educenter_home_section_order  as $key => $value ) {  
			
			if( $value == 'features' ) {

				/**
				 * Features Services Section
				 *
				 * @see educenter_features_services_section() -- 10
				 *
				*/
				do_action( 'educenter_fservices', 10 );

			}

			if( $value == 'aboutus' ) {

				/**
				 * AboutUs Services Section
				 *
				 * @see educenter_aboutus_section() -- 15
				 *
				*/
				do_action( 'educenter_aboutus', 15 );
				
			}

			if( $value == 'cta' ) {

				/**
				 * Call To Action Section
				 *
				 * @see educenter_calltoaction_section() -- 20
				 *
				*/
				do_action( 'educenter_calltoaction', 20 );

			}

			if( $value == 'services' ) {

				/**
				 * Services Section
				 *
				 * @see educenter_services_section() -- 25
				 *
				*/
				do_action( 'educenter_services', 25 );

			}


			if( $value == 'counter' ) {

				/**
				 * Counter Section
				 *
				 * @see educenter_counter_section() -- 30
				 *
				*/
				do_action( 'educenter_counter', 30 );

			}

			if( $value == 'courses' ) {

				/**
				 * Courses Section
				 *
				 * @see educenter_courses_section() -- 35
				 *
				*/
				do_action( 'educenter_courses', 35 );

			}

			if( $value == 'ourblog' ) {
				/**
				 * Our Blog Section
				 *
				 * @see educenter_blog_section() -- 40
				 *
				*/
				do_action( 'educenter_blog', 40 );

			}

			if( $value == 'ourteam' ) {

				/**
				 * Our Team Member Section
				 *
				 * @see educenter_ourteam_section() -- 40
				 *
				*/
				do_action( 'educenter_ourteam', 45 );

			}


			if( $value == 'gallery' ) {

				/**
				 * Gallery Section
				 *
				 * @see educenter_gallery_section() -- 40
				 *
				*/
				do_action( 'educenter_gallery', 50 );

			}

			
			if( $value == 'testimonial' ) {

				/**
				 * Our Testimonials Section
				 *
				 * @see educenter_testimonials_section() -- 40
				 *
				*/
				do_action( 'educenter_testimonials', 55 );

			}

		}//endforeach about section reorder

	}


get_footer();