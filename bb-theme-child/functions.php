<?php

// Defines
define( 'FL_CHILD_THEME_DIR', get_stylesheet_directory() );
define( 'FL_CHILD_THEME_URL', get_stylesheet_directory_uri() );

// Classes
require_once 'classes/class-fl-child-theme.php';

// Actions
add_action( 'wp_enqueue_scripts', 'FLChildTheme::enqueue_scripts', 1000 );

// Core theme options
include('inc/theme-options.php');

// Applies readonly to specific fields - Makes ACF not available for edit in Wordpress Admin 
function my_acf_prepare_field( $field ) {

    // Lock-in the value "Example".
    $field['readonly'] = true;

    return $field;
}
// Calls my_acf_prepare_field function - Makes ACF not available for edit in Wordpress Admin - name=fieldname

add_filter('acf/prepare_field/name=elitestatusgroup', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=dream2020', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=fun7starts7now7', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=beaches2020', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=lvbook2', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=vegas2020', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=vegas20!', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=pixie2021', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=sgetaways1', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=jcb', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=vacation', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=wedding', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=shop', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=dine', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=save', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=acgbook', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=kyam2006', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=harrington2022', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=chacha', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=vegasattractiongroup', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=chaching', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=sunshine', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=bliss', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=nfldraft2022', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=footballdrafteblast', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=flightcentre', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=ilovemmshops', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=t-lanenation', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=routesworld', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=miracleshoppingmex', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=sendmeapostcard', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=hbfoto-lv', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=travel007', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=vegasbaby!', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=5h31by', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=mega2023*las', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=forevervegas', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=westjet', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=florence', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=yavas2022!', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=vivalasvegas', 'my_acf_prepare_field');
add_filter('acf/prepare_field/name=lovevegas', 'my_acf_prepare_field');


function trackCouponCodes() {
    // Gets value of input
    $datav = $_POST['input_1'];
    $datav = strtolower($datav);

    // Get the current value of an option
    $option_value = get_field($datav, 'option');

    // If option exists
    if( isset($option_value) ){
        // Output the current value
        // echo 'Current value: ' . $option_value;

        // Change the value of the option
        $new_option_value = $option_value + 1;
        update_field($datav, $new_option_value, 'option');

        // Get the updated value
        $updated_option_value = get_field($datav, 'option');

        // Output the updated value
        // echo 'Updated value: ' . $updated_option_value;
    } else {
        return;
    }

?>

<?php
}
add_action( 'gform_after_submission_2', 'trackCouponCodes');

