# Local Jungle Automotive Shop Notifications

This plugin is intended for templating notifications for the user filling out the coupon form as well as the admin. Instead of
having to make a notification separately for each of the notifications which can quickly grow to a couple dozen; this
plugin templates them for scalability and maintainability. In order to get started please follow the directions below.

1. Get the [Gravity Forms plugin](https://drive.google.com/file/d/1TW0Glur6TPYFbpH9BGEYSmr2rwWTNRwL/view?usp=drive_link)
   from our Google Drive
2. Install the Gravity Forms plugin in your Wordpress dashboard
3. Install this Local Jungle Notifications plugin in the Wordpress dashboard
4. Navigate to the "Forms > Import/Export" tab in the left hand navigation of your Wordpress dashboard
5. Import the [Coupon Form](coupon-form.json) located in the root folder of this plugin: coupon-form.json
6. Place the form on any page of your choosing
7. Note: Input field #7 is the coupon ID field
8. Note: Input field #9 is the expiration field
9. Find the Form ID that was created after importing the form in step 5 above
10. Replace the $id variable in the lj-form-notifications.php file. This line:

```php
    $id = 1; // Coupon form ID
// Note:  This is at the top of this method 'gform_after_submission'
```

11. In the same file locate this Javascript and replace the 'input_1' with 'input_x' where 'x' is your form ID. For
    example it may be 'input_5_7'

```javascript
    const input = document.getElementById('input_1_7');
// Use your form id and replace x with it 'input_x_7'
```

## Setting the Coupon ID field

This is up to you how to dynamically set this field. However this plugin comes equipped to look for a URL hash (\#coupon). For
example www.mysite.com/#coupon-bmw-oil-change where anything after the '\#coupon' is up to you. But if you use a
custom name it will have to be added to the code in the lj-form-notifications.php file.

### Here is a list of default coupon keys

```php
$coupons = [
			'coupon-bmw-oil-change'        => [
				'subject'     => 'BMW Oil Change Coupon',
				'email_title' => 'PREMIUM BMW OIL CHANGE',
				'price'       => '74.97'
			],
			'coupon-level-one-diagnostic'  => [
				'subject'     => 'LEVEL ONE DIAGNOSTIC',
				'email_title' => 'LEVEL ONE DIAGNOSTIC',
				'price'       => 'Free'
			],
			'coupon-audi-oil-change'       => [
				'subject'     => 'Audi Oil Change Coupon',
				'email_title' => 'PREMIUM AUDI OIL CHANGE',
				'price'       => '74.97'
			],
			'coupon-jaguar-oil-change'     => [
				'subject'     => 'Jaguar Oil Change Coupon',
				'email_title' => 'PREMIUM JAGUAR OIL CHANGE',
				'price'       => '74.97'
			],
			'coupon-landrover-oil-change'  => [
				'subject'     => 'Land Rover Oil Change Coupon',
				'email_title' => 'PREMIUM LAND ROVER OIL CHANGE',
				'price'       => '74.97'
			],
			'coupon-mini-oil-change'       => [
				'subject'     => 'Mini Oil Change Coupon',
				'email_title' => 'PREMIUM MINI OIL CHANGE',
				'price'       => '74.97'
			],
			'coupon-mercedes-a-b-service'  => [
				'subject'     => 'Mercedes A & B Service',
				'email_title' => 'MERCEDES A &amp; B SERVICE',
				'price'       => [ 'service_a' => '297', 'service_b' => '697' ]
			],
			'coupon-porsche-oil-change'    => [
				'subject'     => 'Porsche Oil Change Coupon',
				'email_title' => 'PORSCHE REPAIR OR OIL CHANGE',
				'price'       => '100 OFF'
			],
			'coupon-volkswagen-oil-change' => [
				'subject'     => 'Volkswagen Oil Change Coupon',
				'email_title' => 'PREMIUM VOLKSWAGEN OIL CHANGE',
				'price'       => '74.97'
			],
			'coupon-volvo-oil-change'      => [
				'subject'     => 'Volvo Oil Change Coupon',
				'email_title' => 'PREMIUM VOLVO OIL CHANGE',
				'price'       => '74.97'
			],
			'coupon-european-oil-change'   => [
				'subject'     => 'European Oil Change Coupon',
				'email_title' => 'PREMIUM EUROPEAN OIL CHANGE',
				'price'       => '74.97'
			],
			'coupon-exotics-oil-change'    => [
				'subject'     => 'Premium Exotics Oil Change Coupon',
				'email_title' => 'PREMIUM EXOTICS OIL CHANGE',
				'price'       => '100 OFF'
			],
			'coupon-german-oil-change'     => [
				'subject'     => 'German Oil Change Coupon',
				'email_title' => 'PREMIUM GERMAN OIL CHANGE',
				'price'       => '74.97'
			],
			'coupon-20-off-dealer-quote'   => [
				'subject'     => '20% OFF DEALER QUOTE',
				'email_title' => '20% OFF DEALER QUOTE',
				'price'       => ''
			]
		];
```

## Setting the expiration date field

The expiration date field can be any string you would like. Common examples could be:

* 1/1/2025
* 01/01/2025
* January 1st, 2025
* Jan 1, 2025

However you set this field dynamically is up to you. But you can use
the [Local Jungle Future Date Shortcode plugin](https://drive.google.com/file/d/1RTICasmC4DLAP7QDckxmO0k0EoP2oAMx/view?usp=drive_link)
on the Google Drive. From there you could use some javascript like this to target it and fill the field in.

```javascript
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.coupon .cta').forEach(function (cta) {
    cta.addEventListener('click', function (event) {
      const couponElement = event.target.closest('.coupon');
      let expirationDate = couponElement.querySelector('.lj_future_date')?.innerHTML;
      expirationDate = expirationDate || 'No expiration';
      document.querySelector('input#input_1_7').value = expirationDate;
    });
  });
});

// Note:  Again, the 'input#input_x_7' where x is your form ID
```

The above code snippet assumes each coupon has a wrapper class of '.coupon' and inside of that wrapper is a button/link
with '.cta'. The '.coupon' element mus also contain the future date shortcode class '.lj_future_date'. Essentially, the
HTML would look like so:

```html

<div class="some-container">
    <div class="coupon">
        <!-- Some markup here -->
        <div class="lj_future_date">1/1/2025</div>
        <a class="cta" href="#coupon-bmw-oil-change">Some Link Text</a>
    </div>
</div>
<!-- Note:  All cta links need to have '#coupon' at the beginning.  It is what the javascript looks for.
```