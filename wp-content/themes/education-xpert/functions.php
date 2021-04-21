<?php
/**
 * Describe child theme functions
 *
 * @package Educenter
 * @subpackage Education Xpert
 * 
 */

if ( ! function_exists( 'education_xpert_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function education_xpert_setup() {
    
    $education_xpert_theme_info = wp_get_theme();
    $GLOBALS['education_xpert_version'] = $education_xpert_theme_info->get( 'Version' );

    // This theme uses wp_nav_menu() in one location.
    register_nav_menus( array(
        'menu-3' => esc_html__( 'Top Menu', 'education-xpert' ),
    ) );

}
endif;

add_action( 'after_setup_theme', 'education_xpert_setup' );

/**
 * Managed the theme default color
 */
function education_xpert_customize_register( $wp_customize ) {

	global $wp_customize;

    /**
     * List All Pages
    */
    $slider_pages = array();
    $slider_pages_obj = get_pages();
    $slider_pages[''] = esc_html__('Select Page','education-xpert');
    foreach ($slider_pages_obj as $page) {
      $slider_pages[$page->ID] = $page->post_title;
    }

    /**
     * Slider Feature Services Settings
    */
    $wp_customize->add_section( 'education_xpert_features_services_area_options', array(
      'title'    => esc_html__('Slider Services Settings', 'education-xpert'),
      'priority' => 4,
    ));

        $wp_customize->add_setting( 'education_xpert_feature_services_area_settings', array(
            'sanitize_callback' => 'educenter_sanitize_repeater',
            'default' => json_encode( array(
            array(
                  'fservices_icon' => 'fa fa-desktop',
                  'fservices_page' => '',
                )
            ) )        
        ));

        $wp_customize->add_control( new Educenter_Repeater_Controler( $wp_customize, 'education_xpert_feature_services_area_settings', array(
            'label'   => esc_html__('Features Services Settings Area','education-xpert'),
            'section' => 'education_xpert_features_services_area_options',
            'educenter_box_label' => esc_html__('Features Services','education-xpert'),
            'educenter_box_add_control' => esc_html__('Add New Services','education-xpert'),
        ),
        array(
            'fservices_icon' => array(
                'type'      => 'icon',
                'label'     => esc_html__( 'Select Services Icon', 'education-xpert' ),
                'default'   => 'fa fa-desktop'
            ),
            'fservices_page' => array(
                'type'      => 'select',
                'label'     => esc_html__( 'Select Services Page', 'education-xpert' ),
                'options'   => $slider_pages
            )      
        )));

}

add_action( 'customize_register', 'education_xpert_customize_register', 20 );

/**
 * Enqueue child theme styles and scripts
 */
add_action( 'wp_enqueue_scripts', 'education_xpert_scripts', 20 );

function education_xpert_scripts() {
    
    global $education_xpert_version;
    
    wp_dequeue_style( 'educenter-style' );

    wp_dequeue_style( 'educenter-responsive' );
    
	wp_enqueue_style( 'educenter-parent-style', trailingslashit( esc_url ( get_template_directory_uri() ) ) . '/style.css', esc_attr( $education_xpert_version ) );

    wp_enqueue_style( 'education-xpert-style', get_stylesheet_uri(), esc_attr( $education_xpert_version ) );

    if ( has_header_image() ) {
        $custom_css = '.ed-breadcrumb{ background-image: url("' . esc_url( get_header_image() ) . '"); background-repeat: no-repeat; background-position: center center; background-size: cover; background-attachment: fixed; }';
        wp_add_inline_style( 'education-xpert-style', $custom_css );
    }

    /**
     * Load Animate CSS Library File
    */
    wp_enqueue_style( 'educenter-parent-responsive', trailingslashit( esc_url ( get_template_directory_uri() ) ) . '/assets/css/responsive.css', esc_attr( $education_xpert_version ) );

    if ( is_rtl() ) {
        wp_enqueue_style('educenter-parent-rtl', get_template_directory_uri() .'/rtl.css');
        wp_enqueue_style('education-xpert-rtl', get_stylesheet_directory_uri() .'/rtl.css');
    }

    /**
     * Load Custom Themes JavaScript Library File
    */
    wp_enqueue_script( 'education-xpert-custom', trailingslashit( esc_url ( get_stylesheet_directory_uri()  ) ). '/assets/js/educationpress-custom.js', array('jquery', 'jquery-ui-accordion'), '20151215', true );


    $primary_color = get_theme_mod('educenter_primary_color', '#004A8D');
    
    $rgba = educenter_hex2rgba($primary_color, 0.5);

        $education_xpert = '';

        /**
         *  Background Color
        */         
        $education_xpert .= "
            .ed-courses .ed-img-holder .course_price,
            .general-header .top-header,
            .ed-slider .ed-slide div .ed-slider-info a.slider-button,
            .ed-slider.slider-layout-2 .lSAction>a,

            .general-header .main-navigation>ul>li:before, 
            .general-header .main-navigation>ul>li.current_page_item:before,
            .general-header .main-navigation ul ul.sub-menu,

            .ed-pop-up .search-form input[type='submit'],

            .ed-services.layout-3 .ed-service-slide .col:before,

            .ed-about-us .ed-about-content .listing .icon-holder,

            .ed-cta.layout-1 .ed-cta-holder a.ed-button,

            .ed-cta.layout-1 .ed-cta-holder h2:before,

            h2.section-header:after,
            .ed-services.layout-2 .ed-service-left .ed-col-holder .col .icon-holder:hover,

            .ed-button,

            section.ed-gallery .ed-gallery-wrapper .ed-gallery-item .ed-gallery-button,

            .ed-team-member .ed-team-col .ed-inner-wrap .ed-text-holder h3.ed-team-title:before,

            .ed-testimonials .lSPager.lSpg li a,
            .ed-blog .ed-blog-wrap .lSPager.lSpg li a,

            .ed-blog .ed-blog-wrap .ed-blog-col .ed-title h3:before,

            .goToTop,

            .nav-previous a, 
            .nav-next a,
            .page-numbers,

            #comments form input[type='submit'],

            .widget-ed-title h2:before,

            .widget_search .search-submit, 
            .widget_product_search input[type='submit'],

            .woocommerce #respond input#submit, 
            .woocommerce a.button, 
            .woocommerce button.button, 
            .woocommerce input.button,

            .woocommerce nav.woocommerce-pagination ul li a:focus, 
            .woocommerce nav.woocommerce-pagination ul li a:hover, 
            .woocommerce nav.woocommerce-pagination ul li span.current,

            .woocommerce #respond input#submit.alt, 
            .woocommerce a.button.alt, 
            .woocommerce button.button.alt, 
            .woocommerce input.button.alt,

            .wpcf7 input[type='submit'], 
            .wpcf7 input[type='button'],

            .box-header-nav .main-menu .page_item.current_page_item>a, 
            .box-header-nav .main-menu .page_item:hover>a, 
            .box-header-nav .main-menu>.menu-item.current-menu-item>a, 
            .box-header-nav .main-menu>.menu-item:hover>a,
            .box-header-nav .main-menu .children>.page_item:hover>a, 
            .box-header-nav .main-menu .sub-menu>.menu-item:hover>a,
            .lSSlideOuter .lSPager.lSpg>li.active a, 
            .lSSlideOuter .lSPager.lSpg>li:hover a,
            .ed-services .ed-service-left .ed-col-holder .col h3:before, 
            .ed-courses .ed-text-holder h3:before,
            .ed-team-member .ed-team-col .ed-inner-wrap .ed-text-holder,
            .educenter_counter:before,
            .educenter_counter:after,
            .header-nav-toggle div,

            .edu-features-wrapper .edu-column-wrapper .single-post-wrapper,
            .ed-about-us.layout-2 .ed-about-list h3.ui-accordion-header,
            .ed-about-us.layout-2 .ed-about-list h3.ui-accordion-header:before,
            .not-found .backhome a{

                background-color: $primary_color;

            }\n";


        $education_xpert .= "
            .woocommerce div.product .woocommerce-tabs ul.tabs li:hover, 
            .woocommerce div.product .woocommerce-tabs ul.tabs li.active{

                background-color: $primary_color !important;

            }\n";


        $education_xpert .= "
            .ed-slider .ed-slide div .ed-slider-info a.slider-button:hover,
            .ed-cta.layout-1 .ed-cta-holder a.ed-button:hover{

                background-color: $rgba;

            }\n";

        /**
         *  Color
        */
        $education_xpert .= "
            .ed-services.layout-2 .ed-service-left .ed-col-holder .col .icon-holder i,

            .ed-about-us .ed-about-content .listing .text-holder h3 a:hover,

            .ed-courses .ed-text-holder span,

            section.ed-gallery .ed-gallery-wrapper .ed-gallery-item .ed-gallery-button a i,

            .ed-blog .ed-blog-wrap .ed-blog-col .ed-category-list a,

            .ed-blog .ed-blog-wrap .ed-blog-col .ed-bottom-wrap .ed-tag a:hover, 
            .ed-blog .ed-blog-wrap .ed-blog-col .ed-bottom-wrap .ed-share-wrap a:hover,
            .ed-blog .ed-blog-wrap .ed-blog-col .ed-meta-wrap .ed-author a:hover,

            .page-numbers.current,
            .page-numbers:hover,

            .widget_archive a:hover, 
            .widget_categories a:hover, 
            .widget_recent_entries a:hover, 
            .widget_meta a:hover, 
            .widget_product_categories a:hover, 
            .widget_recent_comments a:hover,

            .woocommerce #respond input#submit:hover, 
            .woocommerce a.button:hover, 
            .woocommerce button.button:hover, 
            .woocommerce input.button:hover,
            .woocommerce ul.products li.product .price,
            .woocommerce nav.woocommerce-pagination ul li .page-numbers,

            .woocommerce #respond input#submit.alt:hover, 
            .woocommerce a.button.alt:hover, 
            .woocommerce button.button.alt:hover, 
            .woocommerce input.button.alt:hover,

            .main-navigation .close-icon:hover,

            .general-header .top-header ul.quickcontact li .fa, 
            .general-header .top-header ul.quickcontact li a .fa,
            .general-header .top-header .right-contact .edu-social li a i:hover,
            .ed-services .ed-service-left .ed-col-holder .col h3 a:hover, 
            .ed-courses .ed-text-holder h3 a:hover,
            .ed-about-us .ed-about-content .listing .text-holder h3 a,
            .ed-testimonials .ed-testimonial-wrap.layout-1 .ed-test-slide .ed-text-holder h3 a:hover,
            .ed-blog .ed-blog-wrap .ed-blog-col .ed-title h3 a:hover,

            .headerone .bottom-header .contact-info .quickcontact .get-tuch i,
            .not-found .backhome a:hover{

                color: $primary_color;

            }\n";


        // $education_xpert .= "
        //     .box-header-nav .main-menu .children>.page_item:hover>a, 
        //     .box-header-nav .main-menu .sub-menu>.menu-item:hover>a{

        //         color: $primary_color !important;

        //     }\n";

        /**
         *  Border Color
        */
        $education_xpert .= "
            .ed-slider .ed-slide div .ed-slider-info a.slider-button,

            .ed-pop-up .search-form input[type='submit'],

            .ed-cta.layout-1 .ed-cta-holder a.ed-button,

            .ed-services.layout-2 .ed-col-holder .col,

            .ed-button,

            .page-numbers,
            .page-numbers:hover,

            .ed-courses.layout-2 .ed-text-holder,

            .ed-testimonials .ed-testimonial-wrap.layout-1 .ed-test-slide .ed-img-holder,
            .ed-testimonials .ed-testimonial-wrap.layout-1 .ed-test-slide .ed-text-holder,

            .goToTop,

            #comments form input[type='submit'],


            .woocommerce #respond input#submit, 
            .woocommerce a.button, 
            .woocommerce button.button, 
            .woocommerce input.button,

            .woocommerce nav.woocommerce-pagination ul li,

            .cart_totals h2, 
            .cross-sells>h2, 
            .woocommerce-billing-fields h3, 
            .woocommerce-additional-fields h3, 
            .related>h2, 
            .upsells>h2, 
            .woocommerce-shipping-fields>h3,

            .woocommerce-cart .wc-proceed-to-checkout a.checkout-button,

            .woocommerce div.product .woocommerce-tabs ul.tabs:before,

            .wpcf7 input[type='submit'], 
            .wpcf7 input[type='button'],

            .ed-slider .ed-slide div .ed-slider-info a.slider-button:hover,
            .educenter_counter,
            .ed-cta.layout-1 .ed-cta-holder a.ed-button:hover,


            .cross-sells h2:before, .cart_totals h2:before, 
            .up-sells h2:before, .related h2:before, 
            .woocommerce-billing-fields h3:before, 
            .woocommerce-shipping-fields h3:before, 
            .woocommerce-additional-fields h3:before, 
            #order_review_heading:before, 
            .woocommerce-order-details h2:before, 
            .woocommerce-column--billing-address h2:before, 
            .woocommerce-column--shipping-address h2:before, 
            .woocommerce-Address-title h3:before, 
            .woocommerce-MyAccount-content h3:before, 
            .wishlist-title h2:before, 
            .woocommerce-account .woocommerce h2:before, 
            .widget-area .widget .widget-title:before,
            .ed-slider .ed-slide div .ed-slider-info a.slider-button,
            .not-found .backhome a,
            .not-found .backhome a:hover{

                border-color: $primary_color;

            }\n";


        $education_xpert .= "

            .nav-next a:after{

                border-left: 11px solid $primary_color;

            }\n";

        $education_xpert .= "
            .nav-previous a:after{

                border-right: 11px solid $primary_color

            }\n";

    wp_add_inline_style( 'education-xpert-style', $education_xpert );
    
}


/**
 * Slider Features Services
*/
if ( ! function_exists( 'education_xpert_slider_features_services' ) ){

    /**
     * Main Banner/Slider Section
     *
     * @since 1.0.0
    */
    function education_xpert_slider_features_services() { 

        $features_services = get_theme_mod('education_xpert_feature_services_area_settings');

        if( $features_services ) { ?>

            <div class="edu-section-wrapper edu-features-wrapper">
                <div class="container">
                    <div class="grid-items-wrapper edu-column-wrapper">
                        <?php
                            $count = 1;
                            
                            $features_services = json_decode( $features_services );

                            foreach($features_services as $features_service){ 

                                $page_id = $features_service->fservices_page;

                            if( !empty( $page_id ) ) {

                                $fservices_page = new WP_Query( 'page_id='.$page_id );

                            if( $fservices_page->have_posts() ) { while( $fservices_page->have_posts() ) { $fservices_page->the_post();

                        ?>
                            <div class="single-post-wrapper edu-column-<?php echo intval( $count );  ?>">
                                <div class="icon-holder">
                                    <i class="<?php echo esc_attr( $features_service->fservices_icon ); ?>"></i>
                                </div>
                                <h3 class="post-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                <?php the_excerpt(); ?>
                            </div>
                        <?php $count++; } } wp_reset_postdata(); } } ?>

                    </div>
                </div>
            </div>
            
<?php } } } 

add_action( 'educenter_action_front_page','education_xpert_slider_features_services', 6 );


/**
 * Footer Widget Function Area
*/
if ( ! function_exists( 'education_xpert_footer_widget_area' ) ) {

    function education_xpert_footer_widget_area(){ ?>
            
            <div class="top-footer layout-1">
                <div class="container">
                    <div class="ed-footer-holder ed-col-3">
                        <?php if ( is_active_sidebar( 'footer-1' ) ) {  dynamic_sidebar( 'footer-1' );  } ?>
                    </div>
                </div>
            </div>

        <?php 
    }
}
add_action( 'education_xpert_footer_widget', 'education_xpert_footer_widget_area', 9 );

