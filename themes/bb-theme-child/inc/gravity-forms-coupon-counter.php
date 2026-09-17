<?php
/**
 * Gravity Forms form 2 ("Coupon Book Access"): after a submission, increment the ACF
 * option whose name matches the submitted coupon code (input_1).
 * Moved out of functions.php 2026-09-17; logic unchanged.
 */

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

}
add_action( 'gform_after_submission_2', 'trackCouponCodes');
