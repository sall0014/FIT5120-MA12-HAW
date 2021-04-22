<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Educenter
 * @subpackage Education Xpert
 *
 */

?>
<!doctype html>
<html <?php language_attributes(); ?> <?php educenter_html_tag_schema(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php 
	if ( function_exists( 'wp_body_open' ) ) {
		wp_body_open();
	} else {
		do_action( 'wp_body_open' );
	}
?>

<div id="page" class="site">

	<a class="skip-link screen-reader-text" href="#content">
		<?php esc_html_e( 'Skip to content', 'education-xpert' ); ?>
	</a>

	<header id="masthead" class="site-header general-header headerone" role="banner" itemscope="itemscope" itemtype="http://schema.org/WPHeader">
		<?php
			$top_header_options = get_theme_mod( 'educenter_top_header', 0 );
	   		if($top_header_options == 1 ){ 
	   	?>
	   		<div class="top-header clearfix">
				<div class="container">
					<div class="contact-info left-contact">
						<div class="edu-topnav">
			              	<?php
				                wp_nav_menu( array(
				                  'theme_location'  => 'menu-3',
				                  'menu_id'         => 'top-menu',
				                  'depth' 			=>1
				                ) );
			              	?>
			          	</div>
					</div>

					<div class="right-contact clearfix">
						<?php do_action( 'educenter_social_links', 5 ); ?>
					</div>
				</div>
			</div>
	   	<?php }  ?>

		<div class="bottom-header">
			<div class="container">
				<div class="header-middle-inner">
					<div class="site-branding logo">
						
						<?php the_custom_logo(); ?>

						<div class="brandinglogo-wrap">
							<h1 class="site-title">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
									<?php bloginfo( 'name' ); ?>
								</a>
							</h1>
							<?php
								$description = get_bloginfo( 'description', 'display' );
								if ( $description || is_customize_preview() ) : ?>
									<p class="site-description"><?php echo $description; /* WPCS: xss ok. */ ?></p>
							<?php endif;  ?>
						</div>

						<div class="header-nav-toggle">
				            <div class="one"></div>
				            <div class="two"></div>
				            <div class="three"></div>
				        </div><!-- Mobile navbar toggler -->

					</div><!-- .site-branding -->
					
					
					<div class="contact-info">
					    <div class="quickcontact">
				        	<?php
				                $email_address    = get_theme_mod('educenter_email_address');
				                $phone_number     = get_theme_mod('educenter_phone_number');
				                $phonenumber      = preg_replace("/[^0-9]/","",$phone_number);
				                $map_address      = get_theme_mod('educenter_map_address');
				                $open_time        = get_theme_mod('educenter_opeening_time');

				                if(!empty( $phone_number )) { ?>

				                	<div class="get-tuch text-left">
				                	    <i class="fas fa-phone-alt"></i>
				                	    <ul>
				                	        <li>
				                	            <h4><?php esc_html_e('Phone Number','education-xpert'); ?></h4>
				                	        </li>
				                	        <li>
				                	        	<p>
					                	            <a href="tel:<?php echo esc_attr( $phonenumber ); ?>">
		        			                            <?php echo esc_attr( $phone_number ); ?>
		        			                        </a>
		        			                    </p>
				                	        </li>
				                	    </ul>
				                	</div>

				            <?php }  if(!empty( $map_address )) { ?>

				            		<div class="get-tuch text-left">
				            		    <i class="fas fa-map-marker-alt"></i>
				            		    <ul>
				            		        <li>
				            		            <h4><?php esc_html_e('Contact Address','education-xpert'); ?></h4>
				            		        </li>
				            		        <li>
				            		            <p><?php echo esc_html( $map_address ); ?></p>
				            		        </li>
				            		    </ul>
				            		</div>
				                    
				            <?php }  if(!empty( $email_address )) { ?>

				            		<div class="get-tuch text-left">
				            		    <i class="far fa-envelope"></i>
				            		    <ul>
				            		        <li>
				            		            <h4><?php esc_html_e('Email Address','education-xpert'); ?></h4>
				            		        </li>
				            		        <li>
				            		            <p>
				            		            	<a href="mailto:<?php echo esc_attr( antispambot( $email_address ) ); ?>">
									                    
									                    <?php echo esc_attr( antispambot( $email_address ) ); ?>
									                </a>
				            		            </p>
				            		        </li>
				            		    </ul>
				            		</div>
				           	
				            <?php }  ?>
					    </div> <!--/ End Contact -->
					</div>
				</div>
			</div>
		</div>
		
		<div class="nav-menu">
			<div class="container">
				<div class="box-header-nav main-menu-wapper">
					<?php
						wp_nav_menu( array(
								'theme_location'  => 'menu-1',
								'menu'            => 'primary-menu',
								'container'       => '',
								'container_class' => '',
								'container_id'    => '',
								'menu_class'      => 'main-menu',
							)
						);
					?>
		        </div>
			</div>
		</div>
		
	</header><!-- #masthead -->

	<div id="content" class="site-content content">
		