<?php
/**
 * Plugin Name: Local Jungle Custom Gravity Forms Notifications
 * Version: 1.0.0
 * Requires at least: 5.5
 * Requires PHP: 7.2
 * Description: Ability to manage email notifications programmatically to reduce the use of notifications in the dashboard.
 * Author: Local Jungle
 * Author URI: https://www.localjungle.com
 * Text Domain: ljgfn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Check if Gravity Forms is active by checking if GFForms class exists
if ( ! class_exists( 'GFForms' ) ) {

	add_action( 'admin_init', function () {
		// Deactivate the plugin if Gravity Forms is not active
		deactivate_plugins( plugin_basename( __FILE__ ) );
	} );

	// Add an admin notice
	add_action( 'admin_notices', function () {
		echo '<div class="error"><p><strong>Local Jungle Notifications</strong>: Gravity Forms is required to run this plugin. Please install and activate Gravity Forms.</p></div>';
	} );

	return; // Stop further execution of the plugin
}

class LJNotifications {
	public mixed $couponForms = [];

	public function __construct() {
		$this->register_hooks();
		$this->couponForms = $this->coupon_forms();
	}

	public function register_hooks() {
		add_action( 'gform_after_submission', [ $this, 'gform_after_submission' ], 10, 2 );
		add_action( 'wp_footer', [ $this, 'wp_footer' ] );
	}

	public function coupon_forms() {
		$form_ids = [ 2 ]; // Set the coupon form(s)

		return apply_filters( 'lj_notifications_form_ids', $form_ids );
	}

	public function gform_after_submission( $entry, $form ) {
		$id = apply_filters( "lj_notifications_form_id", $form['id'], $form );
		if ( ! in_array( $id, $this->couponForms ) ) {
			return;
		}

		do_action( "lj_notifications_after_form_id", $id, $form );

		$fields = [
			'name'        => rgar( $entry, '1' ),
			'email'       => rgar( $entry, '3' ),
			'phone'       => rgar( $entry, '4' ),
			'coupon_id'   => rgar( $entry, '6' ),
			'expiry_date' => rgar( $entry, '7' )
		];

		do_action( "lj_notifications_before_fields_filter", $id, $form );

		$fields = apply_filters( 'lj_notifications_form_fields', $fields, $entry, $form );

		do_action( "lj_notifications_after_fields_filter", $id, $form );

		// These keys are from the form input #7
		$coupons = [
			'coupon-bmw-oil-change'        => [
				'subject'     => 'BMW FULL SYNTHETIC OIL SERVICE',
				'email_title' => 'BMW FULL SYNTHETIC OIL SERVICE',
				'price'       => '$44.97'
			],
//			'coupon-level-one-diagnostic'  => [
//				'subject'     => 'LEVEL ONE DIAGNOSTIC',
//				'email_title' => 'LEVEL ONE DIAGNOSTIC',
//				'price'       => 'Free!'
//			],
			'coupon-audi-oil-change'       => [
				'subject'     => 'Audi Oil Change Coupon',
				'email_title' => 'PREMIUM AUDI OIL CHANGE',
				'price'       => '$44.97'
			],
//			'coupon-jaguar-oil-change'     => [
//				'subject'     => 'Jaguar Oil Change Coupon',
//				'email_title' => 'PREMIUM JAGUAR OIL CHANGE',
//				'price'       => '$74.97'
//			],
			'coupon-landrover-oil-change'  => [
				'subject'     => 'Land Rover Oil Change Coupon',
				'email_title' => 'PREMIUM LAND ROVER OIL CHANGE',
				'price'       => '$44.97'
			],
			'coupon-mini-oil-change'       => [
				'subject'     => 'Mini Oil Change Coupon',
				'email_title' => 'PREMIUM MINI OIL CHANGE',
				'price'       => '$44.97'
			],
			'coupon-mercedes-a-b'          => [
				'subject'     => 'Mercedes A & B Service',
				'email_title' => 'MERCEDES A & B SERVICE',
				'price'       => [ 'service_a' => '$177', 'service_b' => '$377' ]
			],
			'coupon-porsche-oil-change'    => [
				'subject'     => 'Porsche Oil Change Coupon',
				'email_title' => 'PREMIUM PORSCHE OIL CHANGE',
				'price'       => '$44.97'
			],
			'coupon-volkswagen-oil-change' => [
				'subject'     => 'Volkswagen Oil Change Coupon',
				'email_title' => 'PREMIUM VOLKSWAGEN OIL CHANGE',
				'price'       => '$44.97'
			],
			'coupon-mercedes-oil-change' => [
				'subject'     => 'Mercedes Oil Change Coupon',
				'email_title' => 'PREMIUM MERCEDES OIL CHANGE',
				'price'       => '$44.97'
			],
//			'coupon-volvo-oil-change'      => [
//				'subject'     => 'Volvo Oil Change Coupon',
//				'email_title' => 'PREMIUM VOLVO OIL CHANGE',
//				'price'       => '$74.97'
//			],
//			'coupon-european-oil-change'   => [
//				'subject'     => 'European Oil Change Coupon',
//				'email_title' => 'PREMIUM EUROPEAN OIL CHANGE',
//				'price'       => '$74.97'
//			],
//			'coupon-exotics-oil-change'    => [
//				'subject'     => 'Premium Exotics Oil Change Coupon',
//				'email_title' => 'PREMIUM EXOTICS OIL CHANGE',
//				'price'       => '$100 OFF'
//			],
//			'coupon-german-oil-change'     => [
//				'subject'     => 'German Oil Change Coupon',
//				'email_title' => 'PREMIUM GERMAN OIL CHANGE',
//				'price'       => '$74.97'
//			],
			'coupon-20-off-dealer'         => [
				'subject'     => '20% OFF DEALER QUOTE',
				'email_title' => '20% OFF DEALER QUOTE',
				'price'       => ''
			]
		];

		do_action( "lj_notifications_before_coupons_filter", $id, $form, $coupons );

		$coupons = apply_filters( 'lj_form_notifications_coupons', $coupons, $form, $entry );

		do_action( "lj_notifications_after_coupons_filter", $id, $form, $coupons );

		if ( ! in_array( $fields['coupon_id'], array_keys( $coupons ) ) ) {
			return;
		}

		$oil_coupons = apply_filters( 'lj_form_notifications_oil_coupons', $this->get_oil_coupons(), $form, $entry );

		$site_name         = apply_filters( 'lj_form_notifications_site_name', get_bloginfo( 'name' ) );
		$site_url          = apply_filters( 'lj_form_notifications_site_url', get_bloginfo( 'url' ) );
		$page_url          = get_permalink( get_the_ID() );
		$coupon_id         = $fields['coupon_id'];
		$coupon            = $coupons[ $coupon_id ];
		$email_title       = apply_filters( 'lj_form_notifications_email_title', $coupon['email_title'], $coupon );
		$price             = apply_filters( 'lj_form_notifications_price', $coupon['price'], $coupon );
		$company_logo_path = apply_filters( 'lj_notification_logo_path', plugin_dir_url( __FILE__ ) . 'company-logo.jpg' );

//		$to      = 'craig@localjungle.com'; // Set your admin emails here
		$to      = 'chucka@localjungle.com';
		$subject = $coupon['subject'];
		$from    = 'noreply@localjungle.com'; // Set this accordingly
		$message = <<<MESSAGE
<div>
<div style="display: inline-block; position: relative; width: 100%; background: #fff; background-color: #fff; color: #fff; max-width: 350px; height: auto; padding: 15px; font-family: 'Rubik', sans-serif; border: 1px solid #e3e3e3;">
<div style="display: inline-block; color: #ffffff; border: 3px dashed #1997d4; padding: 20px;">
<div style="border: 0; padding: 0;">
<h3 style="color: #000 !important; font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 0;">{$email_title}</h3>
MESSAGE;

		// Does it have a price
		if ( ! empty( $price ) ) {
			if ( 'coupon-mercedes-a-b' == $coupon_id ) {
				$message .= <<<MESSAGE
<h3 style="color: #0296ff !important; font-size: 30px; font-weight: bold; text-transform: uppercase; margin: 15px 0;">{$coupon['price']['service_a']} <span style="color: #000;">SERVICE A</span></h3>
<h3 style="color: #0296ff !important; font-size: 30px; font-weight: bold; text-transform: uppercase; margin: 0; line-height: 30px;">{$coupon['price']['service_b']} <span style="color: #000;">SERVICE B</span></h3>
<p style="font-size: 15px; color: #000; line-height: 22px; margin: 0 0 15px;">Service B Includes a Brake Flush</p>
MESSAGE;

			} else {
				$message .= <<<MESSAGE
<h3 style="color: #0296ff !important; font-size: 30px; font-weight: bold; text-transform: uppercase; margin: 15px 0;">{$price}</h3>
MESSAGE;
			}
		}

		// Is it an oil change coupon
		if ( in_array( $fields['coupon_id'], $oil_coupons ) ) {
			$message .= <<<MESSAGE
<div>
<p style="font-size: 15px; color: #000; line-height: 22px;"><span style="color: #32b0ff; margin-right: 5px;">✔</span>Premium Full Synthetic Oil</p>
<p style="font-size: 15px; color: #000; line-height: 22px;"><span style="color: #32b0ff; margin-right: 5px;">✔</span>OEM Quality Oil Filter Replacement</p>
<p style="font-size: 15px; color: #000; line-height: 22px;"><span style="color: #32b0ff; margin-right: 5px;">✔</span>All Fluid Level Checks &amp; Corrections</p>
<p style="font-size: 15px; color: #000; line-height: 22px;"><span style="color: #32b0ff; margin-right: 5px;">✔</span>Safety Inspection</p>
<p style="font-size: 15px; color: #000; line-height: 22px;"><span style="color: #32b0ff; margin-right: 5px;">✔</span>Reset Maintenance Light/Counter</p>
<p style="font-size: 13px; color: #000; line-height: 16px;">* Up to 5 qts. oil, small extra cost for more oil. Tax and disposal fee extra. Cannot combine with any other offers. Limited time only.</p>

</div>
MESSAGE;

		}

		// Is this the level one diag coupon
		if ( 'coupon-level-one-diagnostic' == $coupon_id ) {
			$message .= <<<MESSAGE
<div>
<p style="font-size: 15px; color: #000; line-height: 22px;"><span style="color: #32b0ff; margin-right: 5px;">✔</span>Free Computer Scan</p>
<p style="font-size: 15px; color: #000; line-height: 22px;"><span style="color: #32b0ff; margin-right: 5px;">✔</span>Check Engine &amp; Dashboard Lights</p>
<p style="font-size: 15px; color: #000; line-height: 22px;"><span style="color: #32b0ff; margin-right: 5px;">✔</span>Alignment Check</p>
<p style="font-size: 15px; color: #000; line-height: 22px;"><span style="color: #32b0ff; margin-right: 5px;">✔</span>Vibration &amp; Noise Check</p>
<p style="font-size: 15px; color: #000; line-height: 22px;"><span style="color: #32b0ff; margin-right: 5px;">✔</span>Brake Inspection</p>
<p style="font-size: 15px; color: #000; line-height: 22px;"><span style="color: #32b0ff; margin-right: 5px;">✔</span>Fluid Leak Check</p>
<p style="font-size: 13px; color: #000; line-height: 16px;">* If parts removal is needed that is a level two diagnostic, which costs extra. We always discuss this with you before doing so. Most diagnostics don’t need parts removal. Expires {$fields['expiry_date']}.</p>
</div>
MESSAGE;

		}

		// Is this the 20 off dealer quote
		if ( 'coupon-20-off-dealer' == $coupon_id ) {
			$message .= <<<MESSAGE
<div>
<p style="font-size: 15px; color: #000; line-height: 22px;"><span style="color: #32b0ff; margin-right: 5px;">✔</span>Bring Us Your Dealership Quote, And We Will Beat It By 20%</p>
<p style="font-size: 13px; color: #000; line-height: 16px;">* Written quotes only. Excludes engine replacements, transmission replacements, tires, batteries, and other exclusions apply. Call for details. Limit $1,000 off. Cannot combine with any other offers.</p>
</div>
MESSAGE;

		}

		$message .= <<<MESSAGE
</div>
</div>
</div>
</div>
<div style="text-decoration: none; color: #000; margin: 15px 0;" title="Call {$site_name}">

Call: <strong style="color: #1997d4;">630-281-4100</strong>
<div style="margin: 15px 5px;"><a title="{$site_name}" href="{$site_url}" target="_blank" rel="noopener"><img style="width: 200px;" src="{$company_logo_path}" alt="{$site_name}" /></a></div>
</div>
MESSAGE;


		$admin_header = <<<MESSAGE
<p class="p_desc"><strong>Name: </strong> {$fields['name']}</p>
<p class="p_desc"><strong>Email: </strong> {$fields['email']}</p>
<p class="p_desc"><strong>Phone: </strong> {$fields['phone']}</p>
<p class="p_desc"><strong>Coupon Name: </strong> {$fields['coupon_id']}</p>
<p class="p_desc"><strong>Page: </strong> {$page_url}</p>
MESSAGE;

		$admin_message = $admin_header . $message;

		$admin_message = apply_filters( 'lj_form_notifications_admin_message', $admin_message, $fields, $coupon, $form, $entry );

		$headers   = [];
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From: ' . $from;
		$headers[] = 'Reply-To: ' . $to;

		$headers = apply_filters( 'lj_form_notifications_admin_headers', $headers, $fields, $coupon, $form, $entry );

		// Send the Administrator Message
		$send = wp_mail( $to, $subject, $admin_message, $headers );

		$headers   = [];
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From: ' . $from;

		$to = $fields['email'];

		$message = apply_filters( 'lj_form_notifications_customer_message', $message, $fields, $coupon, $form, $entry );

		// Send the Customer Email
		$send = wp_mail( $to, $subject, $message, $headers );
		if ( ! $send ) {
			// The email failed so email Craig with the data
			$data    = array_merge( $entry, $coupon );
			$message = json_encode( $data, JSON_PRETTY_PRINT );
			wp_mail( 'craig@localjungle.com', 'EMAIL FAILED TO SEND', $message, $headers );
		}

	}

	public function get_oil_coupons() {
		return [
			'coupon-audi-oil-change',
			'coupon-bmw-oil-change',
			'coupon-landrover-oil-change',
			'coupon-mini-oil-change',
			'coupon-porsche-oil-change',
			'coupon-volkswagen-oil-change',
            'coupon-mercedes-oil-change'
		];
	}

	public function wp_footer() {
		?>
        <script>
          // Takes any incoming URL with '#coupon' starting it and add it to the hidden field if it exists
          document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('a[href^="#coupon"]').forEach(button => {
              button.onclick = function (e) {
                // This is a hidden form field in Gravity Forms
                const input = document.getElementById('input_1_7');
                input.setAttribute('value', e.target.getAttribute('href').replace('#', ''));
              };
            });
          });
        </script>
		<?php
	}
}

new LJNotifications();