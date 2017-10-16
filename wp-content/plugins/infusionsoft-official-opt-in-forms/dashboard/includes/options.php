<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//Array of all sections. All sections will be added into sidebar navigation except for the 'header' section.
$inf_all_sections = array(
	'optin'  => array(
		'title'    => __( 'Opt-In Configuration', 'infusionsoft' ),
		'contents' => array(
			'setup'   => __( 'Setup', 'infusionsoft' ),
			'premade' => __( 'Premade Layouts', 'infusionsoft' ),
			'design'  => __( 'Design', 'infusionsoft' ),
			'display' => __( 'Display Settings', 'infusionsoft' ),
		),
	),
	'header' => array(
		'contents' => array(
			'stats'        => __( 'Opt-In Stats', 'infusionsoft' ),
			'accounts'     => __( 'Account settings', 'infusionsoft' ),
			'importexport' => __( 'Import & Export', 'infusionsoft' ),
			'home'         => __( 'Home', 'infusionsoft' ),
			'edit_account' => __( 'Edit Account', 'infusionsoft' ),
			'support'      => __( 'Help and Support', 'infusionsoft' ),
		),
	),
);

/**
 * Array of all options
 * General format for options:
 * '<option_name>' => array(
 *                            'type' => ...,
 *                            'name' => ...,
 *                            'default' => ...,
 *                            'validation_type' => ...,
 *                            etc
 *                        )
 * <option_name> - just an identifier to add the option into $inf_assigned_options array
 * Array of parameters may contain diffrent attributes depending on option type.
 * 'type' is the required attribute for all options. All other attributes depends on the option type.
 * 'validation_type' and 'name' are required attribute for the option which should be saved into DataBase.
 *
 */

require( 'options_config.php' );

$more_info_hint_text = sprintf( '<a href="%2$s" target="_blank">%1$s</a>',
	__( 'Click here for more information', 'infusionsoft' ), esc_url( 'http://www.infusionsoft.com' ) );

$inf_dashboard_options_all = array(
	'optin_name' => array(
		'section_start' => array(
			'type'      => 'section_start',
			'title'     => __( 'Opt-In Form Name', 'infusionsoft' ),
			'hint_text' => __( 'This name is used to identify your form in the ACTIVE OPT-INS dashboard screen. It won’t appear on the form itself.',
				'infusionsoft' ),
		),

		'option' => array(
			'type'            => 'text',
			'rows'            => '1',
			'name'            => 'optin_name',
			'class'           => 'inf_dashboard_center_webhook_url',
			'placeholder'     => __( 'Enter Opt-In Form Name', 'infusionsoft' ),
			'validation_type' => 'simple_text',
		),
	),

	'form_integration' => array(
		'section_start'         => array(
			'type'  => 'section_start',
			'title' => __( 'Connect Your Email Service Provider', 'infusionsoft' ),
			'class' => 'inf_dashboard_child_hidden inf_dashboard_provider_setup_dropdown',
		),
		'enable_redirect_form'  => array(
			'type'            => 'checkbox',
			'title'           => __( 'Message and Link Only', 'infusionsoft' ),
			'name'            => 'enable_redirect_form',
			'default'         => false,
			'validation_type' => 'boolean',
			'hint_text'       => __( 'Select this if you do not want your banner to include an email opt-in.',
				'infusionsoft' ),
			'class'           => 'inf_dashboard_enable_redirect_form',
			'conditional'     => 'redirect_list_id#email_text#redirect_url#submit_remove,#enable_success_redirect#enable_consent#redirect_bar',
		),
		'email_provider'        => array(
			'type'            => 'select',
			'title'           => __( 'Select Email Provider', 'infusionsoft' ),
			'name'            => 'email_provider',
			'value'           => $email_providers_new_optin,
			'default'         => 'infusionsoft',
			'conditional'     => 'mailchimp_account#aweber_account#constant_contact_account#custom_html#activecampaign#display_name#name_fields#disable_dbl_optin',
			'validation_type' => 'simple_text',
			'class'           => 'inf_dashboard_select_provider',
		),
		'select_account'        => array(
			'type'            => 'select',
			'title'           => __( 'Select Account', 'infusionsoft' ),
			'name'            => 'account_name',
			'value'           => array(
				'empty'       => __( 'Select One...', 'infusionsoft' ),
				'add_account' => __( 'Add Account', 'infusionsoft' )
			),
			'default'         => 'empty',
			'validation_type' => 'simple_text',
			'class'           => 'inf_dashboard_select_account',
		),
		'email_list'            => array(
			'type'            => 'select',
			'title'           => __( 'Select A Tag', 'infusionsoft' ),
			'name'            => 'email_list',
			'value'           => array(
				'empty' => __( 'Select One...', 'infusionsoft' )
			),
			'default'         => 'empty',
			'validation_type' => 'simple_text',
			'class'           => 'inf_dashboard_select_list',
		),
		'custom_html'           => array(
			'type'            => 'text',
			'rows'            => '4',
			'name'            => 'custom_html',
			'placeholder'     => __( 'Insert HTML', 'infusionsoft' ),
			'default'         => '',
			'validation_type' => 'html',
			'display_if'      => 'custom_html'
		),
		'disable_dbl_optin'     => array(
			'type'            => 'checkbox',
			'title'           => __( 'Disable Double Optin', 'infusionsoft' ),
			'name'            => 'disable_dbl_optin',
			'default'         => false,
			'display_if'      => 'mailchimp',
			'validation_type' => 'boolean',
			'hint_text'       => __( 'Abusing this feature may cause your Mailchimp account to be suspended.',
				'infusionsoft' ),
		),
		'center_webhook_url'    => array(
			'type'            => 'input_field',
			'title'           => __( 'Center Webhook Url', 'infusionsoft' ),
			'class'           => 'inf_dashboard_center_webhook_url',
			'name'            => 'center_webhook_url',
			'validation_type' => 'simple_text',
			'default'         => false,
			'hint_text'       => __( 'Requires a <a href="https://center.io" target="_blank">Center Account</a>',
				'infusionsoft' ),
		),
		'submit_webhhok_button' => array(
			'type'  => 'button',
			'title' => __( 'Connect Center', 'infusionsoft' ),
			'link'  => '#',
			'class' => 'webhook_authorize inf_dashboard_icon authorize_service',
		),
	),
	'optin_title'      => array(
		'section_start' => array(
			'type'     => 'section_start',
			'title'    => __( 'Opt-In title', 'infusionsoft' ),
			'subtitle' => __( 'No title will appear if left blank', 'infusionsoft' ),
			'class'    => 'inf_infusionsoft_hide_for_infusionbar',
		),

		'option' => array(
			'type'            => 'text',
			'rows'            => '1',
			'name'            => 'optin_title',
			'class'           => 'inf_dashboard_optin_title inf_dashboard_mce',
			'placeholder'     => __( 'Insert Text', 'infusionsoft' ),
			'default'         => __( 'Subscribe To Our Newsletter', 'infusionsoft' ),
			'validation_type' => 'html',
			'is_wpml_string'  => true,
		),
	),

	'optin_message' => array(
		'section_start' => array(
			'type'     => 'section_start',
			'title'    => __( 'Opt-In message', 'infusionsoft' ),
			'subtitle' => __( 'No message will appear if left blank', 'infusionsoft' ),
		),

		'option' => array(
			'type'            => 'text',
			'rows'            => '3',
			'name'            => 'optin_message',
			'class'           => 'inf_dashboard_optin_message inf_dashboard_mce',
			'placeholder'     => __( 'Insert Text', 'infusionsoft' ),
			'default'         => __( 'Join our mailing list to receive the latest news and updates from our team.',
				'infusionsoft' ),
			'validation_type' => 'html',
			'is_wpml_string'  => true,
		),
	),

	'image_settings' => array(
		'section_start'            => array(
			'type'  => 'section_start',
			'title' => __( 'Image Settings', 'infusionsoft' ),
			'class' => 'inf_dashboard_10_bottom inf_infusionsoft_hide_for_infusionbar',
		),
		'image_orientation'        => array(
			'type'            => 'select',
			'title'           => __( 'Image Orientation', 'infusionsoft' ),
			'name'            => 'image_orientation',
			'value'           => array(
				'no_image' => __( 'No Image', 'infusionsoft' ),
				'above'    => __( 'Image Above Text', 'infusionsoft' ),
				'below'    => __( 'Image Below Text', 'infusionsoft' ),
				'right'    => __( 'Image Right of Text', 'infusionsoft' ),
				'left'     => __( 'Image Left of Text', 'infusionsoft' ),
			),
			'default'         => 'no_image',
			'conditional'     => 'image_upload',
			'validation_type' => 'simple_text',
			'class'           => 'inf_infusionsoft_hide_for_widget inf_dashboard_image_orientation',
		),
		'image_orientation_widget' => array(
			'type'            => 'select',
			'title'           => __( 'Image Orientation', 'infusionsoft' ),
			'name'            => 'image_orientation_widget',
			'value'           => array(
				'no_image' => __( 'No Image', 'infusionsoft' ),
				'above'    => __( 'Image Above Text', 'infusionsoft' ),
				'below'    => __( 'Image Below Text', 'infusionsoft' ),
			),
			'default'         => 'no_image',
			'conditional'     => 'image_upload',
			'validation_type' => 'simple_text',
			'class'           => 'inf_infusionsoft_widget_only_option inf_dashboard_image_orientation_widget',
		),
	),

	'image_upload' => array(
		'section_start'   => array(
			'type'       => 'section_start',
			'name'       => 'image_upload',
			'class'      => 'e_no_top_space inf_infusionsoft_hide_for_infusionbar',
			'display_if' => 'above#below#right#left',
		),
		'image_url'       => array(
			'type'            => 'image_upload',
			'title'           => __( 'Image URL', 'infusionsoft' ),
			'name'            => 'image_url',
			'class'           => 'inf_dashboard_upload_image',
			'button_text'     => __( 'Upload an Image', 'infusionsoft' ),
			'wp_media_title'  => __( 'Choose an Opt-In Image', 'infusionsoft' ),
			'wp_media_button' => __( 'Set as Opt-In Image', 'infusionsoft' ),
			'validation_type' => 'simple_array',
		),
		'image_animation' => array(
			'type'            => 'select',
			'title'           => __( 'Image Load-In Animation', 'infusionsoft' ),
			'name'            => 'image_animation',
			'value'           => array(
				'no_animation' => __( 'No Animation', 'infusionsoft' ),
				'fadein'       => __( 'Fade In', 'infusionsoft' ),
				'slideright'   => __( 'Slide Right', 'infusionsoft' ),
				'slidedown'    => __( 'Slide Down', 'infusionsoft' ),
				'slideup'      => __( 'Slide Up', 'infusionsoft' ),
				'lightspeedin' => __( 'Light Speed', 'infusionsoft' ),
				'zoomin'       => __( 'Zoom In', 'infusionsoft' ),
				'flipinx'      => __( 'Flip', 'infusionsoft' ),
				'bounce'       => __( 'Bounce', 'infusionsoft' ),
				'swing'        => __( 'Swing', 'infusionsoft' ),
				'tada'         => __( 'Tada!', 'infusionsoft' ),
			),
			'hint_text'       => __( 'Define the animation that is used to load the image', 'infusionsoft' ),
			'default'         => 'slideup',
			'validation_type' => 'simple_text',
		),
		'hide_mobile'     => array(
			'type'            => 'checkbox',
			'title'           => __( 'Hide image on mobile', 'infusionsoft' ),
			'name'            => 'hide_mobile',
			'default'         => false,
			'validation_type' => 'boolean',
		),
	),
	'form_setup'   => array(
		'section_start'     => array(
			'type'  => 'section_start',
			'title' => __( 'Form setup', 'infusionsoft' ),
		),
		'display_as_link'   => array(
			'type'            => 'checkbox',
			'title'           => __( 'Display button as link', 'infusionsoft' ),
			'name'            => 'display_as_link',
			'class'           => 'inf_dashboard_display_as_link_checkbox',
			'default'         => false,
			'validation_type' => 'boolean',
		),
		'form_orientation'  => array(
			'type'            => 'select',
			'title'           => __( 'Form Orientation', 'infusionsoft' ),
			'name'            => 'form_orientation',
			'value'           => array(
				'right'  => __( 'Form On Right', 'infusionsoft' ),
				'left'   => __( 'Form On Left', 'infusionsoft' ),
				'bottom' => __( 'Form On Bottom', 'infusionsoft' ),
			),
			'default'         => 'right',
			'validation_type' => 'simple_text',
			'class'           => 'inf_infusionsoft_hide_for_widget inf_infusionsoft_hide_for_infusionbar inf_dashboard_form_orientation',
		),
		'display_name'      => array(
			'type'            => 'checkbox',
			'title'           => __( 'Display Name Field', 'infusionsoft' ),
			'name'            => 'display_name',
			'class'           => 'inf_dashboard_name_checkbox',
			'default'         => false,
			'conditional'     => 'single_name_text',
			'validation_type' => 'boolean',
			'display_if'      => 'getresponse#aweber',
		),
		'redirect_url'      => array(
			'type'            => 'input_field',
			'subtype'         => 'text',
			'name'            => 'redirect_url',
			'class'           => 'inf_dashboard_redirect_url',
			'title'           => __( 'Redirect Url', 'infusionsoft' ),
			'placeholder'     => __( 'http://example.com', 'infusionsoft' ),
			'default'         => '',
			'display_if'      => 'enable_redirect_form#true',
			'validation_type' => 'simple_text',
			'is_wpml_string'  => true,
		),
		'redirect_bar'      => array(
			'type'            => 'select',
			'title'           => __( 'Redirect Type', 'infusionsoft' ),
			'name'            => 'redirect_bar',
			'value'           => array(
				'current_window' => __( 'Current Window', 'infusionsoft' ),
				'new_tab'        => __( 'New Tab', 'infusionsoft' ),
				'new_window'     => __( 'New Window', 'infusionsoft' ),
			),
			'class'           => 'inf_infusionsoft_redirect_bar',
			'default'         => 'new_window',
			'validation_type' => 'simple_text',
			'display_if'      => 'enable_redirect_form#true',
		),
		'infusion_popup'    => array(
			'type'            => 'select',
			'title'           => __( 'Select opt-in to open', 'infusionsoft' ),
			'name'            => 'infusion_popup',
			'value'           => $valid_optins,
			'default'         => 'empty',
			'validation_type' => 'simple_text',
			'class'           => 'inf_dashboard_select_optin inf_infusionsoft_for_infusionbar',
			'hint_text'       => __( 'If selected, redirect will not happen. Will only display popup or flyin forms that are selected to show on everything.',
				'infusionsoft' ),
		),
		'name_fields'       => array(
			'type'            => 'select',
			'title'           => __( 'Name Field(s)', 'infusionsoft' ),
			'name'            => 'name_fields',
			'class'           => 'inf_dashboard_name_fields inf_infusionsoft_hide_for_infusionbar',
			'value'           => array(
				'no_name'         => __( 'No Name Field', 'infusionsoft' ),
				'single_name'     => __( 'Single Name Field', 'infusionsoft' ),
				'first_last_name' => __( 'First + Last Name Fields', 'infusionsoft' ),
			),
			'default'         => 'no_name',
			'conditional'     => 'name_text#last_name#single_name_text',
			'validation_type' => 'simple_text',
			'display_if'      => implode( '#', $show_name_fields ) . '#button_redirect#false',
		),
		'name_text'         => array(
			'type'            => 'input_field',
			'subtype'         => 'text',
			'name'            => 'name_text',
			'class'           => 'inf_dashboard_name_text',
			'title'           => __( 'Name Text', 'infusionsoft' ),
			'placeholder'     => __( 'First Name', 'infusionsoft' ),
			'default'         => '',
			'display_if'      => 'first_last_name',
			'validation_type' => 'simple_text',
			'is_wpml_string'  => true,
		),
		'single_name_text'  => array(
			'type'            => 'input_field',
			'subtype'         => 'text',
			'name'            => 'single_name_text',
			'class'           => 'inf_dashboard_name_text_single',
			'title'           => __( 'Name Text', 'infusionsoft' ),
			'placeholder'     => __( 'Name', 'infusionsoft' ),
			'default'         => '',
			'display_if'      => 'single_name#true',
			'validation_type' => 'simple_text',
			'is_wpml_string'  => true,
		),
		'last_name'         => array(
			'type'            => 'input_field',
			'subtype'         => 'text',
			'name'            => 'last_name',
			'class'           => 'inf_dashboard_last_name_text',
			'title'           => __( 'Last Name Text', 'infusionsoft' ),
			'placeholder'     => __( 'Last Name', 'infusionsoft' ),
			'default'         => '',
			'display_if'      => 'first_last_name',
			'validation_type' => 'simple_text',
			'is_wpml_string'  => true,
		),
		'email_text'        => array(
			'type'            => 'input_field',
			'subtype'         => 'text',
			'name'            => 'email_text',
			'class'           => 'inf_dashboard_email_text',
			'title'           => __( 'Email Text', 'infusionsoft' ),
			'placeholder'     => __( 'Email', 'infusionsoft' ),
			'default'         => '',
			'validation_type' => 'simple_text',
			'is_wpml_string'  => true,
			'display_if'      => 'enable_redirect_form#false'
		),
		'enable_consent'    => array(
			'type'            => 'checkbox',
			'title'           => __( 'Optin Consent', 'infusionsoft' ),
			'name'            => 'enable_consent',
			'default'         => false,
			'class'           => 'inf_infusionsoft_enable_consent',
			'validation_type' => 'boolean',
			'display_if'      => 'enable_redirect_form#false',
			'conditional'     => 'consent_text#consent_color#consent_error',
		),
		'consent_text'      => array(
			'type'            => 'text',
			'subtype'         => 'text',
			'name'            => 'consent_text',
			'class'           => 'inf_dashboard_consent_text',
			'title'           => __( 'Consent Text', 'infusionsoft' ),
			'placeholder'     => __( 'Yes, I consent to receiving direct marketing from this website.',
				'infusionsoft' ),
			'default'         => __( 'Yes, I consent to receiving direct marketing from this website.',
				'infusionsoft' ),
			'validation_type' => 'simple_text',
			'is_wpml_string'  => true,
			'display_if'      => 'enable_consent#true'
		),
		'consent_color'     => array(
			'type'            => 'color_picker',
			'title'           => __( 'Conesent Text Color', 'infusionsoft' ),
			'name'            => 'consent_color',
			'class'           => 'inf_dashboard_consent_color',
			'placeholder'     => __( 'Hex Value', 'infusionsoft' ),
			'default'         => '',
			'validation_type' => 'simple_text',
			'display_if'      => 'enable_consent#true'
		),
		'consent_error'     => array(
			'type'            => 'input_field',
			'subtype'         => 'text',
			'name'            => 'consent_error',
			'class'           => 'inf_dashboard_consent_error',
			'title'           => __( 'Consent Error Text', 'infusionsoft' ),
			'placeholder'     => __( 'Please provide consent.', 'infusionsoft' ),
			'default'         => __( 'Please provide consent.', 'infusionsoft' ),
			'validation_type' => 'simple_text',
			'is_wpml_string'  => true,
			'display_if'      => 'enable_consent#true'
		),
		'button_text'       => array(
			'type'            => 'input_field',
			'subtype'         => 'text',
			'name'            => 'button_text',
			'class'           => 'inf_dashboard_button_text',
			'title'           => __( 'Button Text', 'infusionsoft' ),
			'placeholder'     => __( 'SUBSCRIBE!', 'infusionsoft' ),
			'default'         => '',
			'validation_type' => 'simple_text',
			'is_wpml_string'  => true,
		),
		'button_text_color' => array(
			'type'            => 'select',
			'title'           => __( 'Button Text Color', 'infusionsoft' ),
			'name'            => 'button_text_color',
			'class'           => 'inf_dashboard_field_button_text_color',
			'value'           => array(
				'light' => __( 'Light', 'infusionsoft' ),
				'dark'  => __( 'Dark', 'infusionsoft' ),
			),
			'default'         => 'light',
			'validation_type' => 'simple_text',
		),
		'infusion_position' => array(
			'type'            => 'select',
			'title'           => __( 'Select banner position', 'infusionsoft' ),
			'name'            => 'infusion_position',
			'value'           => $infusion_position,
			'default'         => 'stickytop',
			'validation_type' => 'simple_text',
			'class'           => 'inf_dashboard_select_infusionbar_position',
		),
	),

	'optin_styling' => array(
		'section_start'      => array(
			'type'  => 'section_start',
			'title' => __( 'Opt-In Styling', 'infusionsoft' ),
		),
		'header_bg_color'    => array(
			'type'            => 'color_picker',
			'title'           => __( 'Background Color', 'infusionsoft' ),
			'name'            => 'header_bg_color',
			'class'           => 'inf_dashboard_optin_bg inf_infusionsoft_hide_for_infusionbar',
			'placeholder'     => __( 'Hex Value', 'infusionsoft' ),
			'default'         => '',
			'validation_type' => 'simple_text',
		),
		'header_font'        => array(
			'type'            => 'font_select',
			'title'           => __( 'Header Font', 'infusionsoft' ),
			'name'            => 'header_font',
			'class'           => 'inf_dashboard_header_font inf_infusionsoft_hide_for_infusionbar',
			'validation_type' => 'simple_text',
		),
		'body_font'          => array(
			'type'            => 'font_select',
			'title'           => __( 'Body Font', 'infusionsoft' ),
			'name'            => 'body_font',
			'class'           => 'inf_dashboard_body_font inf_dashboard_for_infusionbar',
			'validation_type' => 'simple_text',
		),
		'header_text_color'  => array(
			'type'            => 'select',
			'title'           => __( 'Text Color', 'infusionsoft' ),
			'name'            => 'header_text_color',
			'class'           => 'inf_dashboard_text_color',
			'value'           => array(
				'light' => __( 'Light Text', 'infusionsoft' ),
				'dark'  => __( 'Dark Text', 'infusionsoft' ),
			),
			'default'         => 'dark',
			'validation_type' => 'simple_text',
		),
		'corner_style'       => array(
			'type'            => 'select',
			'title'           => __( 'Corner Style', 'infusionsoft' ),
			'name'            => 'corner_style',
			'class'           => 'inf_dashboard_corner_style inf_infusionsoft_hide_for_infusionbar',
			'value'           => array(
				'squared' => __( 'Squared Corners', 'infusionsoft' ),
				'rounded' => __( 'Rounded Corners', 'infusionsoft' ),
			),
			'default'         => 'squared',
			'validation_type' => 'simple_text',
		),
		'border_orientation' => array(
			'type'            => 'select',
			'title'           => __( 'Border Orientation', 'infusionsoft' ),
			'name'            => 'border_orientation',
			'class'           => 'inf_dashboard_border_orientation inf_dashboard_for_infusionbar',
			'value'           => array(
				'no_border'  => __( 'No Border', 'infusionsoft' ),
				'full'       => __( 'Full Border', 'infusionsoft' ),
				'top'        => __( 'Top Border', 'infusionsoft' ),
				'right'      => __( 'Right Border', 'infusionsoft' ),
				'bottom'     => __( 'Bottom Border', 'infusionsoft' ),
				'left'       => __( 'Left Border', 'infusionsoft' ),
				'top_bottom' => __( 'Top + Bottom Border', 'infusionsoft' ),
				'left_right' => __( 'Left + Right Border', 'infusionsoft' ),
			),
			'default'         => 'no_border',
			'conditional'     => 'border_color#border_style',
			'validation_type' => 'simple_text',
		),
		'border_color'       => array(
			'type'            => 'color_picker',
			'title'           => __( 'Border Color', 'infusionsoft' ),
			'name'            => 'border_color',
			'class'           => 'inf_dashboard_border_color inf_dashboard_for_infusionbar',
			'placeholder'     => __( 'Hex Value', 'infusionsoft' ),
			'default'         => '',
			'display_if'      => 'full#top#left#right#bottom#top_bottom#left_right',
			'validation_type' => 'simple_text',
		),
	),

	'form_styling' => array(
		'section_start'     => array(
			'type'  => 'section_start',
			'title' => __( 'Form Styling', 'infusionsoft' ),
		),
		'field_orientation' => array(
			'type'            => 'select',
			'title'           => __( 'Form Field Orientation', 'infusionsoft' ),
			'name'            => 'field_orientation',
			'value'           => array(
				'stacked' => __( 'Stacked Form Fields', 'infusionsoft' ),
				'inline'  => __( 'Inline Form Fields', 'infusionsoft' ),
			),
			'default'         => 'inline',
			'validation_type' => 'simple_text',
			'class'           => 'inf_infusionsoft_hide_for_widget inf_dashboard_field_orientation inf_infusionsoft_hide_for_infusionbar',
		),
		'field_corner'      => array(
			'type'            => 'select',
			'title'           => __( 'Form Field Corner Style', 'infusionsoft' ),
			'name'            => 'field_corner',
			'class'           => 'inf_dashboard_field_corners',
			'value'           => array(
				'squared' => __( 'Squared Corners', 'infusionsoft' ),
				'rounded' => __( 'Rounded Corners', 'infusionsoft' ),
			),
			'default'         => 'rounded',
			'validation_type' => 'simple_text',
		),
		'text_color'        => array(
			'type'            => 'select',
			'title'           => __( 'Form Text Color', 'infusionsoft' ),
			'name'            => 'text_color',
			'class'           => 'inf_dashboard_form_text_color inf_infusionsoft_hide_for_infusionbar',
			'value'           => array(
				'light' => __( 'Light Text', 'infusionsoft' ),
				'dark'  => __( 'Dark Text', 'infusionsoft' ),
			),
			'default'         => 'dark',
			'validation_type' => 'simple_text',
		),
		'form_bg_color'     => array(
			'type'            => 'color_picker',
			'title'           => __( 'Form Background Color', 'infusionsoft' ),
			'name'            => 'form_bg_color',
			'class'           => 'inf_dashboard_form_bg_color inf_dashboard_for_infusionbar',
			'placeholder'     => __( 'Hex Value', 'infusionsoft' ),
			'default'         => '',
			'validation_type' => 'simple_text',
		),
		'form_button_color' => array(
			'type'            => 'color_picker',
			'title'           => __( 'Button Color', 'infusionsoft' ),
			'name'            => 'form_button_color',
			'class'           => 'inf_dashboard_form_button_color inf_dashboard_for_infusionbar',
			'placeholder'     => __( 'Hex Value', 'infusionsoft' ),
			'default'         => '',
			'validation_type' => 'simple_text',
		),
	),

	'edge_style' => array(
		'type'            => 'select_shape',
		'title'           => __( 'Choose form edge style', 'infusionsoft' ),
		'name'            => 'edge_style',
		'value'           => array(
			'basic_edge',
			'carrot_edge',
			'wedge_edge',
			'curve_edge',
			'zigzag_edge',
			'breakout_edge',
		),
		'default'         => 'basic_edge',
		'class'           => 'inf_dashboard_optin_edge inf_infusionsoft_hide_for_infusionbar',
		'validation_type' => 'simple_text',
	),

	'border_style' => array(
		'type'            => 'select_shape',
		'title'           => __( 'Choose border style', 'infusionsoft' ),
		'name'            => 'border_style',
		'class'           => 'inf_dashboard_border_style inf_dashboard_for_infusionbar',
		'value'           => array(
			'solid',
			'dashed',
			'double',
			'inset',
			'letter',
		),
		'default'         => 'solid',
		'display_if'      => 'full#top#left#right#bottom#top_bottom#left_right',
		'validation_type' => 'simple_text',
	),

	'footer_text' => array(
		'section_start' => array(
			'type'  => 'section_start',
			'title' => __( 'Form Footer Text', 'infusionsoft' ),
			'class' => 'inf_infusionsoft_hide_for_infusionbar',
		),
		'option'        => array(
			'type'            => 'text',
			'rows'            => '3',
			'name'            => 'footer_text',
			'class'           => 'inf_dashboard_footer_text',
			'placeholder'     => __( 'Insert Your Footer Text', 'infusionsoft' ),
			'default'         => '',
			'validation_type' => 'simple_text',
			'is_wpml_string'  => true,
		),
	),

	'success_message'  => array(
		'section_start'           => array(
			'type'  => 'section_start',
			'title' => __( 'Success Message Text', 'infusionsoft' ),
		),
		'option'                  => array(
			'type'            => 'text',
			'rows'            => '1',
			'name'            => 'success_message',
			'class'           => 'success_message',
			'placeholder'     => __( 'You Have Successfully Subscribed!', 'infusionsoft' ),
			'default'         => '',
			'validation_type' => 'html',
			'is_wpml_string'  => true,
		),
		'enable_success_redirect' => array(
			'type'            => 'checkbox',
			'title'           => __( 'Redirect to URL after opt-in', 'infusionsoft' ),
			'name'            => 'enable_success_redirect',
			'default'         => false,
			'conditional'     => 'success_redirect_section',
			'class'           => 'inf_infusionsoft_success_redirect_enable',
			'validation_type' => 'boolean',
			'display_if'      => 'enable_redirect_form#false',
		),
	),
	'success_redirect' => array(
		'section_start'      => array(
			'type'       => 'section_start',
			'name'       => 'success_redirect_section',
			'title'      => __( 'Success Follow-Up', 'infusionsoft' ),
			'class'      => 'inf_infusionsoft_success_redirect_section',
			'display_if' => 'enable_success_redirect#true',
		),
		'success_url'        => array(
			'type'            => 'input_field',
			'subtype'         => 'text',
			'name'            => 'success_url',
			'class'           => 'inf_dashboard_success_url',
			'title'           => __( 'Success follow-Up url', 'infusionsoft' ),
			'placeholder'     => __( 'http://example.com', 'infusionsoft' ),
			'default'         => '',
			'validation_type' => 'simple_text',
			'is_wpml_string'  => true,
		),
		'success_load_delay' => array(
			'type'            => 'input_field',
			'subtype'         => 'number',
			'title'           => __( 'Delay (in seconds) till redirect', 'infusionsoft' ),
			'name'            => 'success_load_delay',
			'hint_text'       => __( 'Define how many seconds you want to wait before the redirect window opens.',
				'infusionsoft' ),
			'default'         => '5',
			'validation_type' => 'number',
		),
		'redirect_standard'  => array(
			'type'            => 'select',
			'title'           => __( 'Redirect Type', 'infusionsoft' ),
			'name'            => 'redirect_standard',
			'value'           => array(
				'current_window' => __( 'Current Window', 'infusionsoft' ),
				'new_tab'        => __( 'New Tab', 'infusionsoft' ),
				'new_window'     => __( 'New Window', 'infusionsoft' ),
			),
			'class'           => 'inf_infusionsoft_standard_bar',
			'default'         => 'new_window',
			'validation_type' => 'simple_text',
		),
	),
	'custom_css'       => array(
		'section_start' => array(
			'type'  => 'section_start',
			'title' => __( 'Custom CSS', 'infusionsoft' ),
			'class' => 'inf_infusionsoft_hide_for_infusionbar',
		),
		'option'        => array(
			'type'            => 'text',
			'rows'            => '7',
			'name'            => 'custom_css',
			'placeholder'     => __( 'Insert Your Custom CSS Code', 'infusionsoft' ),
			'default'         => '',
			'validation_type' => 'simple_text',
		),
	),

	'load_in' => array(
		'section_start'    => array(
			'type'  => 'section_start',
			'title' => __( 'Display and Timing Settings', 'infusionsoft' ),
			'class' => 'inf_dashboard_for_popup inf_dashboard_for_infusionbar',
		),
		'load_animation'   => array(
			'type'            => 'select',
			'title'           => __( 'Intro Animation', 'infusionsoft' ),
			'name'            => 'load_animation',
			'value'           => array(
				'no_animation' => __( 'No Animation', 'infusionsoft' ),
				'fadein'       => __( 'Fade In', 'infusionsoft' ),
				'slideright'   => __( 'Slide Right', 'infusionsoft' ),
				'slideup'      => __( 'Slide Up', 'infusionsoft' ),
				'slidedown'    => __( 'Slide Down', 'infusionsoft' ),
				'lightspeedin' => __( 'Light Speed', 'infusionsoft' ),
				'zoomin'       => __( 'Zoom In', 'infusionsoft' ),
				'flipinx'      => __( 'Flip', 'infusionsoft' ),
				'bounce'       => __( 'Bounce', 'infusionsoft' ),
				'swing'        => __( 'Swing', 'infusionsoft' ),
				'tada'         => __( 'Tada!', 'infusionsoft' ),
			),
			'hint_text'       => __( 'Define the animation that is used, when you load the page.', 'infusionsoft' ),
			'class'           => 'inf_infusionsoft_load_in_animation inf_infusionsoft_hide_for_infusionbar',
			'default'         => 'fadein',
			'validation_type' => 'simple_text',
		),
		'trigger_auto'     => array(
			'type'            => 'checkbox',
			'title'           => __( 'Trigger After Time Delay', 'infusionsoft' ),
			'name'            => 'trigger_auto',
			'default'         => '1',
			'conditional'     => 'load_delay',
			'validation_type' => 'boolean',
		),
		'load_delay'       => array(
			'type'            => 'input_field',
			'subtype'         => 'number',
			'title'           => __( 'Delay (in seconds)', 'infusionsoft' ),
			'name'            => 'load_delay',
			'hint_text'       => __( 'Define how many seconds you want to wait before the pop up appears on the screen.',
				'infusionsoft' ),
			'default'         => '20',
			'display_if'      => 'true',
			'validation_type' => 'number',
		),
		'trigger_idle'     => array(
			'type'            => 'checkbox',
			'title'           => __( 'Trigger After Inactivity', 'infusionsoft' ),
			'name'            => 'trigger_idle',
			'default'         => false,
			'conditional'     => 'idle_timeout',
			'validation_type' => 'boolean',
			'class'           => 'inf_infusionsoft_hide_for_infusionbar'
		),
		'idle_timeout'     => array(
			'type'            => 'input_field',
			'subtype'         => 'number',
			'title'           => __( 'Idle Timeout ( in seconds )', 'infusionsoft' ),
			'name'            => 'idle_timeout',
			'hint_text'       => __( 'Define how many seconds user should be inactive before the pop up appears on screen.',
				'infusionsoft' ),
			'default'         => '15',
			'display_if'      => 'true',
			'validation_type' => 'number',
			'class'           => 'inf_infusionsoft_hide_for_infusionbar'
		),
		'post_bottom'      => array(
			'type'            => 'checkbox',
			'title'           => __( 'Trigger At The Bottom of Post', 'infusionsoft' ),
			'name'            => 'post_bottom',
			'default'         => '1',
			'validation_type' => 'boolean',
			'class'           => 'inf_infusionsoft_hide_for_infusionbar'
		),
		'comment_trigger'  => array(
			'type'            => 'checkbox',
			'title'           => __( 'Trigger After Commenting', 'infusionsoft' ),
			'name'            => 'comment_trigger',
			'default'         => false,
			'validation_type' => 'boolean',
			'class'           => 'inf_infusionsoft_hide_for_infusionbar'
		),
		'exit_trigger'     => array(
			'type'            => 'checkbox',
			'title'           => __( 'Trigger Before Leaving Page', 'infusionsoft' ),
			'name'            => 'exit_trigger',
			'default'         => false,
			'validation_type' => 'boolean',
			'class'           => 'inf_infusionsoft_hide_for_infusionbar'
		),
		'trigger_scroll'   => array(
			'type'            => 'checkbox',
			'title'           => __( 'Trigger After Scrolling', 'infusionsoft' ),
			'name'            => 'trigger_scroll',
			'default'         => false,
			'conditional'     => 'scroll_pos',
			'validation_type' => 'boolean',
			'class'           => 'inf_infusionsoft_hide_for_infusionbar'
		),
		'scroll_pos'       => array(
			'type'            => 'input_field',
			'subtype'         => 'number',
			'title'           => __( 'Percentage Down The Page', 'infusionsoft' ),
			'name'            => 'scroll_pos',
			'hint_text'       => __( 'Define the % of the page to be scrolled before the pop up appears on the screen.',
				'infusionsoft' ),
			'default'         => '50',
			'display_if'      => 'true',
			'validation_type' => 'number',
			'class'           => 'inf_infusionsoft_hide_for_infusionbar'
		),
		'purchase_trigger' => array(
			'type'            => 'checkbox',
			'title'           => __( 'Trigger After Purchasing', 'infusionsoft' ),
			'name'            => 'purchase_trigger',
			'default'         => false,
			'hint_text'       => __( 'Display on "Thank you" page of WooCommerce after purchase', 'infusionsoft' ),
			'validation_type' => 'boolean',
			'class'           => 'inf_infusionsoft_hide_for_infusionbar'
		),
		'session'          => array(
			'type'            => 'checkbox',
			'title'           => __( 'Display once per session', 'infusionsoft' ),
			'name'            => 'session',
			'default'         => false,
			'validation_type' => 'boolean',
			'conditional'     => 'session_duration',
			'class'           => 'inf_infusionsoft_hide_for_infusionbar'
		),
		'session_duration' => array(
			'type'            => 'input_field',
			'subtype'         => 'number',
			'title'           => __( 'Session Duration (in days)', 'infusionsoft' ),
			'name'            => 'session_duration',
			'hint_text'       => __( 'Define the length of time (in days) that a session lasts for. For example, if you input 2 a user will only see a popup on your site every two days.',
				'infusionsoft' ),
			'default'         => '1',
			'validation_type' => 'number',
			'display_if'      => 'true',
			'class'           => 'inf_infusionsoft_hide_for_infusionbar'
		),
		'hide_mobile'      => array(
			'type'            => 'checkbox',
			'title'           => __( 'Hide on Mobile', 'infusionsoft' ),
			'name'            => 'hide_mobile_optin',
			'default'         => false,
			'validation_type' => 'boolean',
		),
		'click_trigger'    => array(
			'type'            => 'checkbox',
			'title'           => __( 'Trigger When Element is Clicked', 'infusionsoft' ),
			'name'            => 'click_trigger',
			'hint_text'       => __( 'Adds new onclick shortcode option to Infusionsoft editor when editing a page / post',
				'infusionsoft' ),
			'default'         => false,
			'validation_type' => 'boolean',
			'class'           => 'inf_infusionsoft_click_trigger',
			'class'           => 'inf_infusionsoft_hide_for_infusionbar',
		),
		'allow_dismiss'    => array(
			'type'            => 'checkbox',
			'title'           => __( 'Allow user to dismiss', 'infusionsoft' ),
			'hint_text'       => __( 'Allows user to close banner by clicking X' ),
			'name'            => 'allow_dismiss',
			'class'           => 'inf_infusionsoft_allow_dismiss',
			'default'         => true,
			'validation_type' => 'boolean',
		),
		'submit_remove'    => array(
			'type'            => 'checkbox',
			'title'           => __( 'Remove on redirect', 'infusionsoft' ),
			'hint_text'       => __( 'Close the banner on link click' ),
			'name'            => 'submit_remove',
			'class'           => 'inf_infusionsoft_submit_removes',
			'default'         => true,
			'validation_type' => 'boolean',
			'display_if'      => 'enable_redirect_form#true'
		),
	),

	'flyin_orientation' => array(
		'section_start'     => array(
			'type'  => 'section_start',
			'title' => __( 'Fly-In Orientation', 'infusionsoft' ),
			'class' => 'inf_dashboard_for_flyin',
		),
		'flyin_orientation' => array(
			'type'            => 'select',
			'title'           => __( 'Choose Orientation', 'infusionsoft' ),
			'name'            => 'flyin_orientation',
			'value'           => array(
				'right'  => __( 'Right', 'infusionsoft' ),
				'left'   => __( 'Left', 'infusionsoft' ),
				'center' => __( 'Center', 'infusionsoft' ),
			),
			'default'         => 'right',
			'validation_type' => 'simple_text',
		),
	),

	'post_types' => array(
		array(
			'type'  => 'section_start',
			'title' => __( 'Display on', 'infusionsoft' ),
			'class' => 'inf_dashboard_child_hidden display_on_section',
		),
		array(
			'type'            => 'checkbox_set',
			'name'            => 'display_on',
			'value'           => array(
				'everything' => __( 'Everything', 'infusionsoft' ),
				'home'       => __( 'Homepage', 'infusionsoft' ),
				'archive'    => __( 'Archives', 'infusionsoft' ),
				'category'   => __( 'Categories', 'infusionsoft' ),
				'tags'       => __( 'Tags', 'infusionsoft' ),
			),
			'default'         => array( '' ),
			'validation_type' => 'simple_array',
			'conditional'     => array(
				'everything' => 'pages_exclude_section#posts_exclude_section#pages_include_section#posts_include_section',
				'category'   => 'categories_include_section',
			),
			'class'           => 'display_on_checkboxes',
		),
		array(
			'type'            => 'checkbox_posts',
			'subtype'         => 'post_types',
			'name'            => 'post_types',
			'default'         => array( 'post' ),
			'validation_type' => 'simple_array',
			'conditional'     => array(
				'page'     => 'pages_exclude_section',
				'post'     => 'categories_include_section#posts_exclude_section',
				'any_post' => 'posts_exclude_section#categories_include_section',
			),
		),
	),

	'post_categories' => array(
		array(
			'type'       => 'section_start',
			'title'      => __( 'Display on these categories', 'infusionsoft' ),
			'class'      => 'inf_dashboard_child_hidden categories_include_section',
			'name'       => 'categories_include_section',
			'display_if' => 'true',
		),
		array(
			'type'            => 'checkbox_posts',
			'subtype'         => 'post_cats',
			'name'            => 'post_categories',
			'include_custom'  => true,
			'default'         => array(),
			'validation_type' => 'simple_array',
		),
	),

	'pages_exclude' => array(
		array(
			'type'       => 'section_start',
			'title'      => __( 'Do not display on these pages', 'infusionsoft' ),
			'class'      => 'inf_dashboard_child_hidden',
			'name'       => 'pages_exclude_section',
			'display_if' => 'true',
		),
		array(
			'type'            => 'live_search',
			'name'            => 'pages_exclude',
			'post_type'       => 'only_pages',
			'placeholder'     => __( 'Start Typing Page Name...', 'infusionsoft' ),
			'default'         => '',
			'validation_type' => 'simple_text',
		),
	),

	'pages_include' => array(
		array(
			'type'       => 'section_start',
			'title'      => __( 'Display on these pages', 'infusionsoft' ),
			'subtitle'   => __( 'Pages defined below will override all settings above', 'infusionsoft' ),
			'class'      => 'inf_dashboard_child_hidden',
			'name'       => 'pages_include_section',
			'display_if' => 'false',
		),
		array(
			'type'            => 'live_search',
			'name'            => 'pages_include',
			'post_type'       => 'only_pages',
			'placeholder'     => __( 'Start Typing Page Name...', 'infusionsoft' ),
			'default'         => '',
			'validation_type' => 'simple_text',
		),
	),

	'posts_exclude' => array(
		array(
			'type'       => 'section_start',
			'title'      => __( 'Do not display on these posts', 'infusionsoft' ),
			'class'      => 'inf_dashboard_child_hidden',
			'name'       => 'posts_exclude_section',
			'display_if' => 'true',
		),
		array(
			'type'            => 'live_search',
			'name'            => 'posts_exclude',
			'post_type'       => 'only_posts',
			'placeholder'     => __( 'Start Typing Post Name...', 'infusionsoft' ),
			'default'         => '',
			'validation_type' => 'simple_text',
		),
	),

	'posts_include' => array(
		array(
			'type'       => 'section_start',
			'title'      => __( 'Display on these posts', 'infusionsoft' ),
			'subtitle'   => __( 'Posts defined below will override all settings above', 'infusionsoft' ),
			'class'      => 'inf_dashboard_child_hidden',
			'name'       => 'posts_include_section',
			'display_if' => 'false',
		),
		array(
			'type'            => 'live_search',
			'name'            => 'posts_include',
			'post_type'       => 'only_posts',
			'placeholder'     => __( 'Start Typing Post Name...', 'infusionsoft' ),
			'default'         => '',
			'validation_type' => 'simple_text',
		),
	),

	'authorization' => array(
		'authorization_title' => array(
			'type'  => 'main_title',
			'title' => __( 'Setup your accounts', 'infusionsoft' ),
		),

		'sub_section_mailchimp' => array(
			'type'        => 'section_start',
			'sub_section' => true,
			'title'       => __( 'MailChimp', 'infusionsoft' ),
		),

		'mailchimp_key'    => array(
			'type'                 => 'input_field',
			'subtype'              => 'text',
			'name'                 => 'mailchimp_key',
			'title'                => __( 'MailChimp API Key', 'infusionsoft' ),
			'default'              => '',
			'class'                => 'api_option api_option_key',
			'hide_contents'        => true,
			'hint_text'            => $more_info_hint_text,
			'hint_text_with_links' => 'on',
			'validation_type'      => 'simple_text',
		),
		'mailchimp_button' => array(
			'type'      => 'button',
			'title'     => __( 'Authorize', 'Monarch' ),
			'link'      => '#',
			'class'     => 'inf_dashboard_authorize',
			'action'    => 'mailchimp',
			'authorize' => true,
		),

		'sub_section_aweber' => array(
			'type'        => 'section_start',
			'sub_section' => true,
			'title'       => __( 'AWeber', 'infusionsoft' ),
		),

		'aweber_key'    => array(
			'type'                 => 'input_field',
			'subtype'              => 'text',
			'name'                 => 'aweber_key',
			'title'                => __( 'AWeber authorization code', 'infusionsoft' ),
			'default'              => '',
			'class'                => 'api_option api_option_key',
			'hide_contents'        => true,
			'hint_text'            => $more_info_hint_text,
			'hint_text_with_links' => 'on',
			'validation_type'      => 'simple_text',
		),
		'aweber_button' => array(
			'type'      => 'button',
			'title'     => __( 'Authorize', 'Monarch' ),
			'link'      => '#',
			'class'     => 'inf_dashboard_authorize',
			'action'    => 'aweber',
			'authorize' => true,
		),
	),

	'optin_id' => array(
		'type'            => 'hidden_option',
		'subtype'         => 'string',
		'name'            => 'optin_id',
		'validation_type' => 'uniqid'
	),

	'optin_type' => array(
		'type'            => 'hidden_option',
		'subtype'         => 'string',
		'name'            => 'optin_type',
		'validation_type' => 'simple_text',
	),

	'optin_status' => array(
		'type'            => 'hidden_option',
		'subtype'         => 'string',
		'name'            => 'optin_status',
		'validation_type' => 'simple_text',
	),

	'test_status' => array(
		'type'            => 'hidden_option',
		'subtype'         => 'string',
		'name'            => 'test_status',
		'validation_type' => 'simple_text',
	),

	'next_optin' => array(
		'type'            => 'hidden_option',
		'subtype'         => 'string',
		'name'            => 'next_optin',
		'default'         => '-1',
		'validation_type' => 'simple_text',
	),

	'child_of' => array(
		'type'            => 'hidden_option',
		'subtype'         => 'string',
		'name'            => 'child_of',
		'validation_type' => 'simple_text',
	),

	'child_optins' => array(
		'type'            => 'hidden_option',
		'subtype'         => 'array',
		'name'            => 'child_optins',
		'validation_type' => 'simple_array',
	),
	'setup_title'  => array(
		'type'  => 'main_title',
		'title' => __( 'Step 1: Name and Connect Your Form', 'infusionsoft' ),
	),
	'design_title' => array(
		'type'  => 'main_title',
		'title' => __( 'Step 3: Customize Your Form Template', 'infusionsoft' ),
		'class' => 'inf_dashboard_design_title',
	),

	'display_title' => array(
		'type'  => 'main_title',
		'title' => __( 'Step 4: Select When and Where Your Form Appears', 'infusionsoft' ),
	),

	'import_export' => array(
		'type'  => 'import_export',
		'title' => 'Import/Export',
	),

	'home' => array(
		'type'  => 'home',
		'title' => __( 'Home', 'infusionsoft' ),
	),

	'stats' => array(
		'type'  => 'stats',
		'title' => __( 'Opt-In Stats', 'infusionsoft' ),
	),

	'accounts'     => array(
		'type'  => 'account',
		'title' => __( 'Accounts', 'infusionsoft' ),
	),
	'support'      => array(
		'type'  => 'support',
		'title' => __( 'Help and Support', 'infusionsoft' ),
	),
	'edit_account' => array(
		'type'  => 'edit_account',
		'title' => __( 'Edit Account', 'infusionsoft' ),
	),

	'preview_optin' => array(
		'type'  => 'preview_optin',
		'title' => __( 'Preview', 'infusionsoft' ),
	),

	'premade_templates_start' => array(
		'type'     => 'main_title',
		'title'    => __( 'Step 2: Select a Template To Customize', 'infusionsoft' ),
		'subtitle' => __( 'Choose a template that best represents your basic style preference. Don’t worry if you can’t find the exact color or image. You’ll be able to customize these elements to your liking in the next step.',
			'infusionsoft' ),
	),

	'premade_templates_main' => array(
		'type'  => 'premade_templates',
		'title' => __( 'Choose a template', 'infusionsoft' ),
	),

	'end_of_section' => array(
		'type' => 'section_end',
	),

	'end_of_sub_section' => array(
		'type'        => 'section_end',
		'sub_section' => 'true',
	),
);

/**
 * Array of options assigned to sections. Format of option key is following:
 *    <section>_<sub_section>_options
 * where:
 *    <section> = $inf_ -> $key
 *    <sub_section> = $inf_ -> $value['contents'] -> $key
 *
 * Note: name of this array shouldn't be changed. $inf_assigned_options variable is being used in INF_Dashboard class
 * as options container.
 */
$inf_assigned_options = array(
	'optin_setup_options'         => array(
		$inf_dashboard_options_all['setup_title'],
		$inf_dashboard_options_all['optin_id'],
		$inf_dashboard_options_all['optin_type'],
		$inf_dashboard_options_all['optin_status'],
		$inf_dashboard_options_all['test_status'],
		$inf_dashboard_options_all['child_of'],
		$inf_dashboard_options_all['child_optins'],
		$inf_dashboard_options_all['next_optin'],
		$inf_dashboard_options_all['optin_name']['section_start'],
		$inf_dashboard_options_all['optin_name']['option'],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['form_integration']['section_start'],
		$inf_dashboard_options_all['form_integration']['enable_redirect_form'],
		$inf_dashboard_options_all['form_integration']['email_provider'],
		$inf_dashboard_options_all['form_integration']['select_account'],
		$inf_dashboard_options_all['form_integration']['email_list'],
		$inf_dashboard_options_all['form_integration']['custom_html'],
		$inf_dashboard_options_all['form_integration']['disable_dbl_optin'],
		//$inf_dashboard_options_all[ 'form_integration' ][ 'center_webhook_url' ],
		//$inf_dashboard_options_all[ 'form_integration' ][ 'submit_webhhok_button' ],
		$inf_dashboard_options_all['end_of_section'],
	),
	'optin_premade_options'       => array(
		$inf_dashboard_options_all['premade_templates_start'],
		$inf_dashboard_options_all['premade_templates_main'],
	),
	'optin_design_options'        => array(
		$inf_dashboard_options_all['preview_optin'],
		$inf_dashboard_options_all['design_title'],
		$inf_dashboard_options_all['optin_title']['section_start'],
		$inf_dashboard_options_all['optin_title']['option'],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['optin_message']['section_start'],
		$inf_dashboard_options_all['optin_message']['option'],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['image_settings']['section_start'],
		$inf_dashboard_options_all['image_settings']['image_orientation'],
		$inf_dashboard_options_all['image_settings']['image_orientation_widget'],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['image_upload']['section_start'],
		$inf_dashboard_options_all['image_upload']['image_url'],
		$inf_dashboard_options_all['image_upload']['image_animation'],
		$inf_dashboard_options_all['image_upload']['hide_mobile'],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['optin_styling']['section_start'],
		$inf_dashboard_options_all['form_setup']['infusion_position'],
		$inf_dashboard_options_all['optin_styling']['header_bg_color'],
		$inf_dashboard_options_all['optin_styling']['header_font'],
		$inf_dashboard_options_all['optin_styling']['body_font'],
		$inf_dashboard_options_all['optin_styling']['header_text_color'],
		$inf_dashboard_options_all['optin_styling']['corner_style'],
		$inf_dashboard_options_all['optin_styling']['border_orientation'],
		$inf_dashboard_options_all['optin_styling']['border_color'],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['border_style'],
		$inf_dashboard_options_all['form_setup']['section_start'],
		$inf_dashboard_options_all['form_setup']['form_orientation'],
		$inf_dashboard_options_all['form_setup']['redirect_url'],
		$inf_dashboard_options_all['form_setup']['redirect_bar'],
		$inf_dashboard_options_all['form_setup']['infusion_popup'],
		$inf_dashboard_options_all['form_setup']['display_name'],
		$inf_dashboard_options_all['form_setup']['name_fields'],
		$inf_dashboard_options_all['form_setup']['name_text'],
		$inf_dashboard_options_all['form_setup']['single_name_text'],
		$inf_dashboard_options_all['form_setup']['last_name'],
		$inf_dashboard_options_all['form_setup']['email_text'],
		$inf_dashboard_options_all['form_setup']['button_text'],
		$inf_dashboard_options_all['form_setup']['enable_consent'],
		$inf_dashboard_options_all['form_setup']['consent_color'],
		$inf_dashboard_options_all['form_setup']['consent_text'],
		$inf_dashboard_options_all['form_setup']['consent_error'],
		$inf_dashboard_options_all['form_setup']['display_as_link'],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['form_styling']['section_start'],
		$inf_dashboard_options_all['form_styling']['field_orientation'],
		$inf_dashboard_options_all['form_styling']['field_corner'],
		$inf_dashboard_options_all['form_styling']['text_color'],
		$inf_dashboard_options_all['form_styling']['form_bg_color'],
		$inf_dashboard_options_all['form_styling']['form_button_color'],
		$inf_dashboard_options_all['form_setup']['button_text_color'],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['edge_style'],
		$inf_dashboard_options_all['footer_text']['section_start'],
		$inf_dashboard_options_all['footer_text']['option'],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['success_message']['section_start'],
		$inf_dashboard_options_all['success_message']['option'],
		$inf_dashboard_options_all['success_message']['enable_success_redirect'],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['success_redirect']['section_start'],
		$inf_dashboard_options_all['success_redirect']['success_url'],
		$inf_dashboard_options_all['success_redirect']['success_load_delay'],
		$inf_dashboard_options_all['success_redirect']['redirect_standard'],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['custom_css']['section_start'],
		$inf_dashboard_options_all['custom_css']['option'],
		$inf_dashboard_options_all['end_of_section'],
	),
	'optin_display_options'       => array(
		$inf_dashboard_options_all['display_title'],
		$inf_dashboard_options_all['flyin_orientation']['section_start'],
		$inf_dashboard_options_all['flyin_orientation']['flyin_orientation'],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['load_in']['section_start'],
		$inf_dashboard_options_all['load_in']['load_animation'],
		$inf_dashboard_options_all['load_in']['trigger_auto'],
		$inf_dashboard_options_all['load_in']['load_delay'],
		$inf_dashboard_options_all['load_in']['trigger_idle'],
		$inf_dashboard_options_all['load_in']['idle_timeout'],
		$inf_dashboard_options_all['load_in']['post_bottom'],
		$inf_dashboard_options_all['load_in']['comment_trigger'],
		$inf_dashboard_options_all['load_in']['exit_trigger'],
		$inf_dashboard_options_all['load_in']['click_trigger'],
		$inf_dashboard_options_all['load_in']['trigger_scroll'],
		$inf_dashboard_options_all['load_in']['scroll_pos'],
		$inf_dashboard_options_all['load_in']['purchase_trigger'],
		$inf_dashboard_options_all['load_in']['session'],
		$inf_dashboard_options_all['load_in']['session_duration'],
		$inf_dashboard_options_all['load_in']['hide_mobile'],
		$inf_dashboard_options_all['load_in']['allow_dismiss'],
		$inf_dashboard_options_all['load_in']['submit_remove'],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['post_types'][0],
		$inf_dashboard_options_all['post_types'][1],
		$inf_dashboard_options_all['post_types'][2],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['post_categories'][0],
		$inf_dashboard_options_all['post_categories'][1],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['pages_include'][0],
		$inf_dashboard_options_all['pages_include'][1],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['pages_exclude'][0],
		$inf_dashboard_options_all['pages_exclude'][1],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['posts_exclude'][0],
		$inf_dashboard_options_all['posts_exclude'][1],
		$inf_dashboard_options_all['end_of_section'],
		$inf_dashboard_options_all['posts_include'][0],
		$inf_dashboard_options_all['posts_include'][1],
		$inf_dashboard_options_all['end_of_section'],
	),
	'header_importexport_options' => array(
		$inf_dashboard_options_all['import_export'],
	),
	'header_home_options'         => array(
		$inf_dashboard_options_all['home'],
	),
	'header_accounts_options'     => array(
		$inf_dashboard_options_all['accounts'],
	),
	'header_edit_account_options' => array(
		$inf_dashboard_options_all['edit_account'],
	),
	'header_stats_options'        => array(
		$inf_dashboard_options_all['stats'],
	),
	'header_support_options'      => array(
		$inf_dashboard_options_all['support'],
	),
);
