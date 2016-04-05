<?php
/**
 *
 * PHP Pro Bid $Id$ HyajvpuBN6uFN3wyXswiDeUVsJixnW7KSvSDOD2o/dY=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * This file will contain all static sentences that are not included plus navigation.xml files labels,
 * so that the gettext parser can parse them automatically.
 */

// Cube\Validate\Db\NoRecordExists
_("A record matching '%value%' has been found.");

// Cube\Validate\Db\RecordExists
_("No record matching '%value%' has been found.");

// Cube\Validate\Alphanumeric
_("'%s' must contain an alphanumeric value.");

// Cube\Validate\Captcha
_('The captcha code is not valid.');

// Cube\Validate\Csrf
_('The CSRF validation has failed.');

// Cube\Validate\Digits
_("'%s' can only contain digits.");

// Cube\Validate\Email
_("'%s' does not contain a valid email address.");

// Cube\Validate\GreaterThan
_("'%s' must be greater or equal to %value%.");
_("'%s' must be greater than %value%.");

// Cube\Validate\Identical
_("'%s' and '%name%' do not match.");

// Cube\Validate\InArray
_("'%s' was not found in the haystack.");

// Cube\Validate\LessThan
_("'%s' must be smaller than %value%.");
_("'%s' must be smaller or equal to %value%.");

// Cube\Validate\NoHtml
_("'%s' cannot contain any html code.");
// Cube\Validate\NotEmpty
_("'%s' is required and cannot be empty.");

// Cube\Validate\Numeric
_("'%s' must contain a numeric value.");

// Cube\Validate\Phone
_("'%s' does not contain a valid phone number.");

// Cube\Validate\StringLength
_("'%s' expects a string, invalid type given.");
_("'%s' must contain at least %value% characters.");
_("'%s' must contain no more than %value% characters.");

// Cube\Validate\Url
_("'%s' does not contain a valid URL.");

// Ppb\Db\Table\Row\Bid
_('High Bid');
_('Outbid');

// Ppb\Db\Table\Row\Offer
_('Pending');
_('Accepted');
_('Declined');
_('Withdrawn');

// Ppb\Db\Table\Row\Sale
_('Unpaid');
_('Paid');
_('Paid (Direct Payment)');
_('Payment on Arrival');
_('Processing');
_('Posted/Sent');
_('Problem');
_('N/A');

// Ppb\Db\Table\Row\User
_('Admin');
_('Manager');

// Ppb\Form\Element\MultiUpload
_('or enter remote URL');
_('Add');
_('or enter embedded code');

// Ppb\Model\Elements\PaymentGateway\AmazonPayments
_('AmazonPayments');
_('Click to pay using Amazon Payments Checkout.');

// Ppb\Model\Elements\PaymentGateway\AuthorizeNet
_('AuthorizeNet');
_('Click to pay through Authorize.net.');

// Ppb\Model\Elements\PaymentGateway\Nochex
_('Nochex');
_('Click to pay through Nochex.');

// Ppb\Model\Elements\PaymentGateway\Paymate
_('Paymate');
_('Click to pay using Paymate Express Payments.');

// Ppb\Model\Elements\PaymentGateway\PaymentSimulator
_('PaymentSimulator');
_('Payment Simulator description.');

// Ppb\Model\Elements\PaymentGateway\PayPal
_('PayPal');
_('Click to pay through PayPal.');

// Ppb\Model\Elements\PaymentGateway\PayPal
_('PayPalSandbox');
_('Click to pay through PayPal Sandbox.');

// Ppb\Model\Elements\PaymentGateway\SagePay
_('SagePay');
_('Click to pay through SagePay.');
// Ppb\Model\Elements\PaymentGateway\Skrill
_('Skrill');
_('Click to pay through Skrill.');
// Ppb\Model\Elements\PaymentGateway\TCheckout
_('TCheckout');
_('Click to pay through 2Checkout.');

// Ppb\Model\Elements\PaymentGateway\WorldPay
_('WorldPay');
_('Click to pay though WorldPay.');

// Ppb\Model\Shipping\Carrier\AustraliaPost
_('AustraliaPost');
_('Australia Post Description');

// Ppb\Model\Shipping\Carrier\FedExWebServices
_('FedExWebServices');
_('FedExWebServices Description');

// Ppb\Model\Shipping\Carrier\UPS
_('UPS');
_('UPS Description');

// Ppb\Model\Shipping\Carrier\USPS
_('USPS');
_('USPS Description');

// Ppb\Model\Shipping
_('Pick-up');
_('Standard Shipping');
_('Lbs');
_('Kg');
_('No pick-ups');
_('Buyer can pick-up');
_('Buyer must pick-up');
_('Offer Free Postage');
_('If amount exceeds');
_('Postage Calculation Type');
_('First Item');
_('Additional Items');
_('Select Shipping Carriers');
_('Weight UOM');
_('Shipping Locations');
_('Location Groups');
_('Accept Returns');
_('Return Policy Details');
_('Pick-ups');
_('Postage');
_('Item Weight');
_('Insurance');
_('Shipping Instructions');

// Ppb\Service\Fees\ListingSetup
_('Listing Setup');
_('Home Page Featuring');
_('Category Pages Featuring');
_('Highlighted Item');
_('Bold Item');
_('Listing Images');
_('Listing Media');
_('Digital Downloads');
_('Additional Category Listing');
_('Buy Out');
_('Reserve Price');
_('Make Offer');
_('Item Swap');

// Ppb\Service\Fees\SaleTransaction
_('Sale Transaction Fee');

// Ppb\Service\Fees\StoreSubscription
_('Store Subscription Fee');

// Ppb\Service\Fees\UserSignup
_('User Signup Fee');

// Ppb\Service\Fees\UserVerification
_('User Verification Fee');

// Ppb\Service\Listings
_("%s listings have been opened.");
_("%s listings have been closed.");
_("%s listings have been relisted.");
_("%s listings have been activated.");
_("%s listings have been suspended.");
_("%s listings have been approved.");
_("%s listings have been undeleted.");
_("%s listings have been deleted.");

// Ppb\Service\ListingsMedia
_('Image');
_('Video');
_('Download');

// Ppb\Service\Messaging
_('Public Question - Listing ID: #%s');
_('Private Message - Listing ID: #%s');
_('Sale Transaction - Invoice ID: #%s');
_('Message from Site Admin');
_('Abuse Report - User: %s');
_('Abuse Report - Listing ID: %s');
_('Refund Request - Invoice ID: #%s');

// @version 7.2
_('Re:');
_('Fwd:');

// Ppb\Service\Newsletters
_('All Users');
_('Active Users');
_('Suspended Users');
_('Newsletter Subscribers');
_('Store Owners');

// Ppb\Service\Reputation
_('Positive');
_('Neutral');
_('Negative');
_('Last Month');
_('Last 6 Months');
_('Last 12 Months');

// Ppb\Service\Users
_('User Verification Subscription');
_('Store Subscription');

// Ppb\Validate\PaymentMethods
_('You must select at least one method of payment.');

// Ppb\View\Helper\Countdown
_('days');
_('day');
_('years');
_('year');
_('months');
_('month');
_('weeks');
_('week');
_('hours');
_('hour');
_('hrs');
_('hr');
_('minutes');
_('minute');
_('mins');
_('min');
_('seconds');
_('second');
_('secs');
_('sec');

// App\Form\Contact
_('Send');

// App\Form\Payment
_('Make Payment');

// Listings\Form\Cart
_('Place Order');
_('Update Cart');
_('Checkout');
_('Submit');

// Listings\Form\Listing
_('Next Step');
_('Previous Step');
_('List Now');
_('Save as Draft');

// Members\Form\FeesCalculator
_('Calculate');

// Members\Form\Invoices
_('Update Values');

// Members\Form\Login
_('Login');

// Members\Form\PostageSetup
_('Save Settings');

// @version 7.3
// App\Controller\Payment
_('User Signup Fee - User ID: #%s');
_('Listing Setup Fee - Listing ID: #%s');
_('Sale Transaction Fee - Sale ID: #%s');
_('Direct Payment Purchase - Invoice ID: #%s');
_('Credit Account - User ID: #%s');
_('Store Subscription Fee - %s Store - User ID: #%s');
_('User Verification Fee - User ID: #%s');

// js/global.js
_('OK');
_('Cancel');

// @version 7.5
// Admin/config/data/navigation/navigation.xml
_("Home");
_("Settings");
_("Site Setup");
_("User Settings");
_("Registration & Verification");
_("Signup Confirmation");
_("Account Settings");
_("Private Reputation Comments");
_("Users Reputation");
_("Site Settings");
_("Home Page Appearance");
_("Time and Date");
_("Multi Language Support");
_("SEO Settings");
_("Cron Jobs");
_("Private Site/Single Seller");
_("Users Messaging");
_("Preferred Sellers Feature");
_("Site Invoices Settings");
_("Google Analytics");
_("Allow Buyer to Combine Purchases");
_("Social Network Links");
_("Cookie Usage Confirmation");
_("Google reCAPTCHA");
_("BCC Emails to Admin");
_("Recently Viewed Listings Box");
_("Bulk Lister");
_("Listings Settings");
_("Global Settings");
_("Currency Settings");
_("Listing Subtitle");
_("Listing Images");
_("Media Uploads");
_("Listings Approval");
_("Auto Relist Settings");
_("Marked Deleted Listings Removal");
_("Closed Listings Deletion");
_("Additional Category Listing");
_("Listings Counters");
_("Terms and Conditions Box");
_("Display Free Fees on User End");
_("Custom Start & End Times");
_("Search Engine Settings");
_("Auctions & Products");
_("Auctions Settings");
_("Products Settings");
_("Buy Out Feature");
_("Make Offer Feature");
_("Shipping Settings");
_("User Phone Numbers Display");
_("Seller's Other Items Box");
_("Digital Downloads");
_("Sale Transaction Fee Refunds");
_("Users");
_("Site Users");
_("Create Account");
_("Edit Account");
_("Admin Users");
_("Users Reputation Management");
_("Listings");
_("All Listings");
_("Auctions");
_("Products");
_("Sales Management");
_("Edit Listing");
_("Stores");
_("Subscriptions");
_("Manage Stores");
_("Tables");
_("Categories");
_("Locations");
_("Listings Durations");
_("Bid Increments");
_("Currencies");
_("Offline Payment Methods");
_("Content Sections");
_("Custom Fields");
_("Auctions / Products");
_("User Registration");
_("Create Custom Field");
_("Edit Custom Field");
_("Site Content");
_("Content Pages");
_("Create Content Page");
_("Edit Content Page");
_("Adverts Management");
_("Create Advert");
_("Edit Advert");
_("Edit System Emails");
_("Edit Language Files");
_("Fees");
_("Payment Gateways");
_("Fees Management");
_("General");
_("User Signup");
_("Listing Setup Fee");
_("Sale Fee");
_("Home Page Featuring Fee");
_("Category Pages Featuring Fee");
_("Highlighted Listing Fee");
_("Listing Images Fee");
_("Media Upload Fee");
_("Digital Downloads Fee");
_("Additional Category Listing Fee");
_("Reserve Price Fee");
_("Buy Out Fee");
_("Make Offer Fee");
_("Tax");
_("Configuration");
_("Tools");
_("Accounting");
_("View Invoice");
_("Messaging");
_("View Conversation");
_("Who's Online");
_("Newsletters");
_("Create Newsletter");
_("Edit Newsletter");
_("Vouchers");
_("Create Voucher");
_("Edit Voucher");
_("Shipping Carriers Management");
_("Word Filter");
_("SEO Link Redirects");
_("Filter Site Users");
_("All");
_("Active");
_("Suspended");
_("Awaiting Approval");
_("Email Not Verified");
_("Accounts with Debit");
_("Debit Balance Exceeded");
_("Verified Users");
_("Seller Accounts");
_("Preferred Sellers");
_("Store Owners");
_("Filter Auctions Products");
_("Open");
_("Scheduled");
_("Closed");
_("Marked Deleted");
_("Sold Items");
_("Account History Filter");
_("Receipt");
_("Debit");
_("Credit");
_("Pending Refund Requests");
_("Messaging Filter");
_("Inbox");
_("Sent");
_("Users Messages");

// App/config/data/navigation/navigation.xml
_("Home");
_("Members Area");
_("Summary");
_("Messages");
_("Inbox");
_("Sent");
_("Archive");
_("Received");
_("View Conversation");
_("Post Message");
_("Buying");
_("Purchases");
_("Current Bids");
_("Offers");
_("Buyer Tools");
_("Wishlist");
_("Favorite Stores");
_("Selling");
_("Open");
_("Scheduled");
_("Closed");
_("Drafts");
_("My Sales");
_("Seller Tools");
_("Global Settings");
_("Fees Calculator");
_("Postage Setup");
_("Prefilled Fields");
_("Seller Vouchers");
_("Create Voucher");
_("Edit Voucher");
_("Bulk Lister");
_("Store");
_("Store Setup");
_("Store Pages");
_("Custom Categories");
_("Feedback");
_("Leave Feedback");
_("Pending Feedback");
_("Feedback Left");
_("My Account");
_("Personal Information");
_("Account Settings");
_("User Verification");
_("Account History");
_("Address Book");
_("Buy");
_("Sell");
_("Stores");
_("Browse Stores");
_("Register");
_("Login");
_("Buying Bids Filter");
_("All");
_("Winning");
_("Outbid");
_("Selling Open Filter");
_("With Bids");
_("With Offers");
_("Sold");
_("Selling Closed Filter");
_("Pending");
_("Not Sold");
_("Offers Filter");
_("Accepted");
_("Declined");
_("Withdrawn");
_("Sale Invoices Filter");
_("Unpaid");
_("Paid");
_("Posted/Sent");
_("Account History Filter");
_("Receipt");
_("Debit");
_("Credit");
_("Pending Refund Requests");
_("Bulk Lister Tabs");
_("Description");
_("File Structure");
_("Categories");
_("Locations");
_("Payment Methods");

// @version 7.5
// Cube\Form\Element\Select
_("-- select --");

// @version 7.6
_("Site");
_("Both");

// @version 7.7
_("IP Address");
_("Email Address");
_("Register / Log In");
_("Purchasing");

function _($string)
{
}
