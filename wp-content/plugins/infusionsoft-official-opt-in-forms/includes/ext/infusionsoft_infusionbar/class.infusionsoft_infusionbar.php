<?php

class infusionsoft_infusionbar
{
	public function __construct(){
		add_action('wp_enqueue_scripts', array($this, 'register_infusionbar_scripts'));
		add_action('admin_init', array($this, 'register_infusionbar__admin_scripts'));
	}

	public static function generate_form_content($optin_id, $page_id, $pagename = '', $details = array())
	{

		if (empty($details)) {
			$all_optins = INF_Infusionsoft::get_infusionsoft_options();
			$details = $all_optins[$optin_id];
		}
		if (isset($_COOKIE['hubspotutk'])) {
			$hubspot_cookie = $_COOKIE['hubspotutk'];
		} else {
			$hubspot_cookie = '';
		}
		//unsetting uneeded array elements
		$details['image_url'] = null;

		$hide_img_mobile_class = isset($details['hide_mobile']) && '1' == $details['hide_mobile'] ? 'inf_infusionsoft_hide_mobile' : '';
		$image_animation_class = isset($details['image_animation'])
			? esc_attr(' inf_infusionsoft_image_' . $details['image_animation'])
			: 'inf_infusionsoft_image_no_animation';
		$image_class = $hide_img_mobile_class . $image_animation_class . ' inf_infusionsoft_image';

		// Translate all strings if WPML is enabled
		if (function_exists('icl_translate')) {
			$optin_title = '';
			$optin_message = icl_translate('infusionsoft', 'optin_message_' . $optin_id, $details['optin_message']);
			$email_text = icl_translate('infusionsoft', 'email_text_' . $optin_id, $details['email_text']);
			$first_name_text = '';
			$single_name_text = '';
			$last_name_text = '';
			$button_text = icl_translate('infusionsoft', 'button_text_' . $optin_id, $details['button_text']);
			$success_text = icl_translate('infusionsoft', 'success_message_' . $optin_id, $details['success_message']);
			$footer_text = '';
		} else {
			$optin_title = '';
			$optin_message = $details['optin_message'];
			$email_text = $details['email_text'];
			$first_name_text = '';
			$single_name_text = '';
			$last_name_text = '';
			$button_text = $details['button_text'];
			$success_text = $details['success_message'];
			$footer_text = '';
		}

		$formatted_title = '&lt;h2&gt;&nbsp;&lt;/h2&gt;' != $details['optin_title']
			? str_replace('&nbsp;', '', $optin_title)
			: '';
		$formatted_message = '' != $details['optin_message'] ? $optin_message : '';
		$formatted_footer = '' != $details['footer_text']
			? sprintf(
				'<div class="inf_infusionsoft_form_footer">
					<p>%1$s</p>
				</div>',
				stripslashes(esc_html($footer_text))
			)
			: '';

		$is_single_name = (isset($details['display_name']) && '1' == $details['display_name']) ? false : true;
		$output = sprintf('
			<div class="inf_infusionsoft_infusionbar_form_container_wrapper clearfix">
				<div class="inf_infusionsoft_infusionbar_header_outer">
					<div class="inf_infusionsoft_infusionbar_form_header%13$s">
						%3$s
						%4$s
						<div class="inf_infusionsoft_success_container">
						<h2 class="inf_infusionsoft_success_message">%9$s</h2>
						</div>

					</div>
				</div>
				<div class="inf_infusionsoft_infusionbar_form_content%6$s%7$s%12$s"%11$s>
					%8$s
					%10$s
				</div>
			</div>
			%14$s',
			('right' == $details['image_orientation'] || 'left' == $details['image_orientation']) && 'widget' !== $details['optin_type']
				? sprintf(' split%1$s', 'right' == $details['image_orientation']
				? ' image_right'
				: '')
				: '',
			(('above' == $details['image_orientation'] || 'right' == $details['image_orientation'] || 'left' == $details['image_orientation']) && 'widget' !== $details['optin_type']) || ('above' == $details['image_orientation_widget'] && 'widget' == $details['optin_type'])
				? sprintf(
				'%1$s',
				empty($details['image_url']['id'])
					? sprintf(
					'<img src="%1$s" alt="%2$s" %3$s>',
					esc_attr($details['image_url']['url']),
					esc_attr(wp_strip_all_tags(html_entity_decode($formatted_title))),
					'' !== $image_class
						? sprintf('class="%1$s"', esc_attr($image_class))
						: ''
				)
					: wp_get_attachment_image($details['image_url']['id'], 'infusionsoft_image', false, array('class' => $image_class))
			)
				: '',
			('' !== $formatted_message)
				? sprintf(
				'<div class="inf_infusionsoft_form_text">
						%1$s
					</div>',
				stripslashes(html_entity_decode($formatted_message, ENT_QUOTES, 'UTF-8'))
			)
				: '',
			('below' == $details['image_orientation'] && 'widget' !== $details['optin_type']) || (isset($details['image_orientation_widget']) && 'below' == $details['image_orientation_widget'] && 'widget' == $details['optin_type'])
				? sprintf(
				'%1$s',
				empty($details['image_url']['id'])
					? sprintf(
					'<img src="%1$s" alt="%2$s" %3$s>',
					esc_attr($details['image_url']['url']),
					esc_attr(wp_strip_all_tags(html_entity_decode($formatted_title))),
					'' !== $image_class ? sprintf('class="%1$s"', esc_attr($image_class)) : ''
				)
					: wp_get_attachment_image($details['image_url']['id'], 'infusionsoft_image', false, array('class' => $image_class))
			)
				: '', //#5
			('no_name' == $details['name_fields'] && !INF_Infusionsoft::is_only_name_support($details['email_provider'])) || (INF_Infusionsoft::is_only_name_support($details['email_provider']) && $is_single_name)
				? ' inf_infusionsoft_1_field'
				: sprintf(
				' inf_infusionsoft_%1$s_fields',
				'first_last_name' == $details['name_fields'] && !INF_Infusionsoft::is_only_name_support($details['email_provider'])
					? '3'
					: '2'
			),
			'inline' == $details['field_orientation'] && 'bottom' == $details['form_orientation'] && 'widget' !== $details['optin_type']
				? ' inf_infusionsoft_bottom_inline'
				: '',
			('stacked' == $details['field_orientation'] && 'bottom' == $details['form_orientation']) || 'widget' == $details['optin_type']
				? ' inf_infusionsoft_bottom_stacked'
				: '',
			'custom_html' == $details['email_provider']
				? stripslashes(html_entity_decode($details['custom_html']))
				: sprintf('
					%1$s
					<form method="post" class="clearfix">
						%3$s
						<p class="inf_infusionsoft_infusionbar_input inf_infusionsoft_subscribe_email %16$s">
							<input placeholder="%2$s">
						</p>
						<button data-optin_type="infusion" data-optin_id="%4$s" data-service="%5$s" data-list_id="%6$s" data-page_id="%7$s" data-post_name="%12$s" data-cookie="%13$s" data-account="%8$s" data-disable_dbl_optin="%11$s" data-redirect_url="%15$s%17$s" data-redirect="%25$s" data-success_delay="%18$s" %19$s %21$s class="infusionbar_submit_button %14$s%20$s%24$s" %23$s>
							<span class="inf_infusionsoft_subscribe_loader"></span>
							<span class="inf_infusionsoft_button_text inf_infusionsoft_button_text_color_%10$s">%9$s</span>
						</button>
					</form>',
				'basic_edge' == $details['edge_style'] || '' == $details['edge_style']
					? ''
					: INF_Infusionsoft::get_the_edge_code($details['edge_style'], 'widget' == $details['optin_type'] ? 'bottom' : $details['form_orientation']),
				'' != $email_text ? stripslashes(esc_attr($email_text)) : esc_html__('Email', 'infusionsoft'),
				('no_name' == $details['name_fields'] && !INF_Infusionsoft::is_only_name_support($details['email_provider'])) || (INF_Infusionsoft::is_only_name_support($details['email_provider']) && $is_single_name)
					? ''
					: sprintf(
					'<p class="inf_infusionsoft_infusionbar_input inf_infusionsoft_subscribe_name">
								<input placeholder="%1$s%2$s" maxlength="50">
							</p>%3$s',
					'first_last_name' == $details['name_fields']
						? sprintf(
						'%1$s',
						'' != $first_name_text
							? stripslashes(esc_attr($first_name_text))
							: esc_html__('First Name', 'infusionsoft')
					)
						: '',
					('first_last_name' != $details['name_fields'])
						? sprintf('%1$s', '' != $single_name_text
						? stripslashes(esc_attr($single_name_text))
						: esc_html__('Name', 'infusionsoft')) : '',
					'first_last_name' == $details['name_fields'] && !INF_Infusionsoft::is_only_name_support($details['email_provider'])
						? sprintf('
									<p class="inf_infusionsoft_infusionbar_input inf_infusionsoft_subscribe_last">
										<input placeholder="%1$s" maxlength="50">
									</p>',
						'' != $last_name_text ? stripslashes(esc_attr($last_name_text)) : esc_html__('Last Name', 'infusionsoft')
					)
						: ''
				),
				esc_attr($optin_id),
				esc_attr($details['email_provider']), //#5
				esc_attr($details['email_list']),
				esc_attr($page_id),
				isset($details['account_name']) ? $details['account_name'] : 'blank',
				'' != $button_text ? stripslashes(esc_html($button_text)) : esc_html__('SUBSCRIBE!', 'infusionsoft'),
				isset($details['button_text_color']) ? esc_attr($details['button_text_color']) : '', // #10
				isset($details['disable_dbl_optin']) && '1' === $details['disable_dbl_optin'] ? 'disable' : '',#11
				esc_attr($pagename),#12
				esc_attr($hubspot_cookie),#13
				(isset($details['enable_redirect_form']) && $details['enable_redirect_form'] == true)? 'inf_infusionsoft_redirect_page' : 'inf_infusionsoft_submit_subscription',#14
				(isset($details['enable_redirect_form']) && $details['enable_redirect_form'] == true) ? esc_url($details['redirect_url']) : '',#15
				(isset($details['enable_redirect_form']) && $details['enable_redirect_form'] == true) ? 'hidden_item' : '',#16
				isset($details['success_url']) ? esc_url($details['success_url']) : '',#17 //you will notice both 15 and 17 exist in the dat-redirect_url attribute. This is because both should never be set at the same time.
				isset($details['success_load_delay']) ? esc_attr($details['success_load_delay']) : '', #18
				isset($details['infusionbar_popup']) && $details['infusionbar_popup'] !='nopopup' ? sprintf(
				'data-popup_id = %1$s', $details['infusionbar_popup']) : '', #19
				(isset($details['display_as_link']) && $details['display_as_link'] == true) ? ' btnaslink_'.$details['button_text_color'].'' : '',#20
				(isset($details['submit_remove']) && $details['submit_remove'] == true) ? ' data-submit_remove="true" ': 'data-submit_remove="false"',#21
				(isset($details['redirect_new_tab_bar']) && $details['redirect_new_tab_bar'] == true) ? 'new_tab' : '', #22
			  	(isset($details['enable_consent']) && $details['enable_consent'] == true) ? '' : '',#23
			  	(isset($details['enable_consent']) && $details['enable_consent'] == true) ? ' cursor-not-allowed' : '',#24
			    (isset($details['enable_redirect_form']) && $details['enable_redirect_form'] == true) ? esc_attr($details['redirect_bar']) : esc_attr($details['redirect_standard'])#25


			),
			'' != $success_text
				? stripslashes(esc_html($success_text))
				: esc_html__('You have Successfully Subscribed!', 'infusionsoft'), //#10
			$formatted_footer,
			'custom_html' == $details['email_provider']
				? sprintf(
				' data-optin_id="%1$s" data-service="%2$s" data-list_id="%3$s" data-page_id="%4$s" data-account="%5$s"',
				esc_attr($optin_id),
				'custom_form',
				'custom_form',
				esc_attr($page_id),
				'custom_form'
			)
				: '',
			'custom_html' == $details['email_provider'] ? ' inf_infusionsoft_custom_html_form' : '',
			isset($details['header_text_color'])
				? sprintf(
				' inf_infusionsoft_header_text_%1$s',
				esc_attr($details['header_text_color'])
			)
				: ' inf_infusionsoft_header_text_dark', //#13
			(isset($details['allow_dismiss']) && $details['allow_dismiss'] == '1') ? sprintf(
				'<div class="close_ctr"><span class="inf_infusionsoft_close_button %1$s"></span></div>',
				esc_attr($details['header_text_color'])
			) : '' #14
		);

		return $output;
	}

	public static function generate_custom_css($form_class, $single_optin = array())
	{
		$font_functions = INF_Infusionsoft::load_fonts_class();
		$custom_css = '';

		if (isset($single_optin['form_bg_color']) && '' !== $single_optin['form_bg_color']) {
			$custom_css .= $form_class . ' .inf_infusionsoft_infusionbar_container, .inf_infusionsoft_infusionbar_form_container_wrapper, .inf_infusionsoft_infusionbar_form_header .inf_infusionsoft_form_text { background-color: ' . $single_optin['form_bg_color'] . ' !important; } ';
			if ('zigzag_edge' === $single_optin['edge_style']) {
				$custom_css .=
					$form_class . ' .zigzag_edge .inf_infusionsoft_infusionbar_form_content:before { background: linear-gradient(45deg, transparent 33.33%, ' . $single_optin['form_bg_color'] . ' 33.333%, ' . $single_optin['form_bg_color'] . ' 66.66%, transparent 66.66%), linear-gradient(-45deg, transparent 33.33%, ' . $single_optin['form_bg_color'] . ' 33.33%, ' . $single_optin['form_bg_color'] . ' 66.66%, transparent 66.66%) !important; background-size: 20px 40px !important; } ' .
					$form_class . ' .zigzag_edge.inf_infusionsoft_form_right .inf_infusionsoft_infusionbar_form_content:before, ' . $form_class . ' .zigzag_edge.inf_infusionsoft_form_left .inf_infusionsoft_infusionbar_form_content:before { background-size: 40px 20px !important; }
					@media only screen and ( max-width: 767px ) {' .
					$form_class . ' .zigzag_edge.inf_infusionsoft_form_right .inf_infusionsoft_infusionbar_form_content:before, ' . $form_class . ' .zigzag_edge.inf_infusionsoft_form_left .inf_infusionsoft_infusionbar_form_content:before { background: linear-gradient(45deg, transparent 33.33%, ' . $single_optin['form_bg_color'] . ' 33.333%, ' . $single_optin['form_bg_color'] . ' 66.66%, transparent 66.66%), linear-gradient(-45deg, transparent 33.33%, ' . $single_optin['form_bg_color'] . ' 33.33%, ' . $single_optin['form_bg_color'] . ' 66.66%, transparent 66.66%) !important; background-size: 20px 40px !important; } ' .
					'}';
			}
		}

		if (isset($single_optin['header_bg_color']) && '' !== $single_optin['header_bg_color']) {
			$custom_css .= $form_class . ' .inf_infusionsoft_form_container .inf_infusionsoft_form_header { background-color: ' . $single_optin['header_bg_color'] . ' !important; } ';

			switch ($single_optin['edge_style']) {
				case 'curve_edge' :
					$custom_css .= $form_class . ' .curve_edge .curve { fill: ' . $single_optin['header_bg_color'] . '} ';
					break;

				case 'wedge_edge' :
					$custom_css .= $form_class . ' .wedge_edge .triangle { fill: ' . $single_optin['header_bg_color'] . '} ';
					break;

				case 'carrot_edge' :
					$custom_css .=
						$form_class . ' .carrot_edge .inf_infusionsoft_infusionbar_form_content:before { border-top-color: ' . $single_optin['header_bg_color'] . ' !important; } ' .
						$form_class . ' .carrot_edge.inf_infusionsoft_form_right .inf_infusionsoft_infusionbar_form_content:before, ' . $form_class . ' .carrot_edge.inf_infusionsoft_form_left .inf_infusionsoft_infusionbar_form_content:before { border-top-color: transparent !important; border-left-color: ' . $single_optin['header_bg_color'] . ' !important; }
						@media only screen and ( max-width: 767px ) {' .
						$form_class . ' .carrot_edge.inf_infusionsoft_form_right .inf_infusionsoft_infusionbar_form_content:before, ' . $form_class . ' .carrot_edge.inf_infusionsoft_form_left .inf_infusionsoft_infusionbar_form_content:before { border-top-color: ' . $single_optin['header_bg_color'] . ' !important; border-left-color: transparent !important; }
						}';
					break;
			}

			if ('dashed' === $single_optin['border_style']) {
				if ('breakout_edge' !== $single_optin['edge_style']) {
					$custom_css .= $form_class . ' .inf_infusionsoft_form_container { background-color: ' . $single_optin['header_bg_color'] . ' !important; } ';
				} else {
					$custom_css .= $form_class . ' .inf_infusionsoft_header_outer { background-color: ' . $single_optin['header_bg_color'] . ' !important; } ';
				}
			}
		}
		//print_r($single_optin);die();
		if (isset($single_optin['form_button_color']) && '' !== $single_optin['form_button_color'] && isset($single_optin['display_as_link']) && $single_optin['display_as_link'] != 1) {
			$custom_css .= $form_class . ' .inf_infusionsoft_infusionbar_form_content button { background-color: ' . $single_optin['form_button_color'] . ' !important; } ';
		}

		if (isset($single_optin['border_color']) && '' !== $single_optin['border_color'] && 'no_border' !== $single_optin['border_orientation']) {
			$infusionbar_class = '.inf_infusionsoft_infusionbar';
			switch ($single_optin['border_style']) {
				case 'letter' :
					$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_letter { background: repeating-linear-gradient( 135deg, ' . $single_optin['border_color'] . ', ' . $single_optin['border_color'] . ' 10px, #fff 10px, #fff 20px, #f84d3b 20px, #f84d3b 30px, #fff 30px, #fff 40px ) !important; } ';
					break;

				case 'double' :
					$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_double { -moz-box-shadow: inset 0 0 0 6px ' . $single_optin['header_bg_color'] . ', inset 0 0 0 8px ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 0 0 6px ' . $single_optin['header_bg_color'] . ', inset 0 0 0 8px ' . $single_optin['border_color'] . '; box-shadow: inset 0 0 0 6px ' . $single_optin['header_bg_color'] . ', inset 0 0 0 8px ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';

					switch ($single_optin['border_orientation']) {
						case 'top' :
							$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_double.inf_infusionsoft_border_position_top { -moz-box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
							break;

						case 'right' :
							$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_double.inf_infusionsoft_border_position_right { -moz-box-shadow: inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
							break;

						case 'bottom' :
							$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_double.inf_infusionsoft_border_position_bottom { -moz-box-shadow: inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
							break;

						case 'left' :
							$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_double.inf_infusionsoft_border_position_left { -moz-box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
							break;

						case 'top_bottom' :
							$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_double.inf_infusionsoft_border_position_top_bottom { -moz-box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . ', inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . ', inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 8px 0 0 ' . $single_optin['border_color'] . ', inset 0 -6px 0 0 ' . $single_optin['header_bg_color'] . ', inset 0 -8px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
							break;

						case 'left_right' :
							$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_double.inf_infusionsoft_border_position_left_right { -moz-box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . ', inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . ', inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset 8px 0 0 0 ' . $single_optin['border_color'] . ', inset -6px 0 0 0 ' . $single_optin['header_bg_color'] . ', inset -8px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['border_color'] . '; } ';
					}
					break;

				case 'inset' :
					$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_inset { -moz-box-shadow: inset 0 0 0 3px ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 0 0 3px ' . $single_optin['border_color'] . '; box-shadow: inset 0 0 0 3px ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';

					switch ($single_optin['border_orientation']) {
						case 'top' :
							$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_inset.inf_infusionsoft_border_position_top { -moz-box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
							break;

						case 'right' :
							$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_inset.inf_infusionsoft_border_position_right { -moz-box-shadow: inset -3px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset -3px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset -3px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
							break;

						case 'bottom' :
							$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_inset.inf_infusionsoft_border_position_bottom { -moz-box-shadow: inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
							break;

						case 'left' :
							$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_inset.inf_infusionsoft_border_position_left { -moz-box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
							break;

						case 'top_bottom' :
							$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_inset.inf_infusionsoft_border_position_top_bottom { -moz-box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . ', inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . ', inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 0 3px 0 0 ' . $single_optin['border_color'] . ', inset 0 -3px 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
							break;

						case 'left_right' :
							$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_inset.inf_infusionsoft_border_position_left_right { -moz-box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . ', inset -3px 0 0 0 ' . $single_optin['border_color'] . '; -webkit-box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . ', inset -3px 0 0 0 ' . $single_optin['border_color'] . '; box-shadow: inset 3px 0 0 0 ' . $single_optin['border_color'] . ', inset -3px 0 0 0 ' . $single_optin['border_color'] . '; border-color: ' . $single_optin['header_bg_color'] . '; } ';
					}
					break;

				case 'solid' :
					$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_solid { border-color: ' . $single_optin['border_color'] . ' !important; border-style: solid !important;  } ';
					break;

				case 'dashed' :
					$custom_css .= $infusionbar_class . '.inf_infusionsoft_border_dashed  { border-color: ' . $single_optin['border_color'] . ' !important; } ';
					break;
			}

		}

		$custom_css .= (isset($single_optin['enable_redirect_form']) && $single_optin['enable_redirect_form'] == '1' ) ? '
		.inf_power_box_mode_infusionbar, .inf_infusionsoft_preview_infusionbar .inf_power_box_mode_infusionbar, inf_power_box_mode_infusionbar .inf_power_logo, .inf_infusionsoft_preview_infusionbar .inf_power_box_mode_infusionbar .inf_power_logo, inf_infusionsoft_infusionbar, .inf_infusionsoft_preview_infusionbar, inf_infusionsoft_infusionbar_form_container_wrapper, .inf_infusionsoft_preview_infusionbar .inf_infusionsoft_infusionbar_form_container_wrapper, inf_infusionsoft_infusionbar_form .inf_infusionsoft_form_container, .inf_infusionsoft_preview_infusionbar .inf_infusionsoft_infusionbar_form .inf_infusionsoft_form_container, .inf_infusionsoft_infusionbar_form_header, .inf_infusionsoft_infusionbar_form_content, .inf_infusionsoft_preview_infusionbar .inf_infusionsoft_infusionbar_form_header, .inf_infusionsoft_infusionbar_form_content{ min-height:35px} .inf_infusionsoft_infusionbar_form .inf_infusionsoft_form_text, .inf_infusionsoft_preview_infusionbar .inf_infusionsoft_infusionbar_form .inf_infusionsoft_form_text, .inf_infusionsoft_infusionbar_form .inf_infusionsoft_form_text p, .inf_infusionsoft_preview_infusionbar .inf_infusionsoft_form_text p, .inf_power_infusionsoft a .inf_power_logo:after, .inf_infusionsoft_preview_infusionbar .inf_power_infusionsoft a .inf_power_logo:after{} .inf_infusionsoft_infusionbar_form .inf_infusionsoft_submit_subscription, .inf_infusionsoft_infusionbar_form .inf_infusionsoft_redirect_page, .inf_infusionsoft_preview_infusionbar  .inf_infusionsoft_submit_subscription, .inf_infusionsoft_preview_infusionbar .inf_infusionsoft_redirect_page{ height: 30px;} .inf_infusionsoft .inf_infusionsoft_infusionbar_form .inf_infusionsoft_close_button, .inf_infusionsoft_preview_infusionbar .inf_infusionsoft_close_button{ top: 0 !important;}'
			:'.inf_power_box_mode_infusionbar, .inf_infusionsoft_preview_infusionbar .inf_power_box_mode_infusionbar, .inf_power_box_mode_infusionbar .inf_power_logo, .inf_infusionsoft_preview_infusionbar .inf_power_box_mode_infusionbar .inf_power_logo, inf_infusionsoft_infusionbar, .inf_infusionsoft_preview_infusionbar, inf_infusionsoft_infusionbar_form_container_wrapper, .inf_infusionsoft_preview_infusionbar .inf_infusionsoft_infusionbar_form_container_wrapper, inf_infusionsoft_infusionbar_form .inf_infusionsoft_form_container, .inf_infusionsoft_preview_infusionbar .inf_infusionsoft_infusionbar_form .inf_infusionsoft_form_container, .inf_infusionsoft_infusionbar_form_header, .inf_infusionsoft_infusionbar_form_content, .inf_infusionsoft_preview_infusionbar .inf_infusionsoft_infusionbar_form_header, .inf_infusionsoft_infusionbar_form_content{ min-height:50px} .inf_infusionsoft_infusionbar_form .inf_infusionsoft_form_text, .inf_infusionsoft_preview_infusionbar .inf_infusionsoft_infusionbar_form .inf_infusionsoft_form_text, .inf_infusionsoft_infusionbar_form .inf_infusionsoft_form_text p, .inf_infusionsoft_preview_infusionbar .inf_infusionsoft_form_text p, .inf_power_infusionsoft a .inf_power_logo:after, .inf_infusionsoft_preview_infusionbar .inf_power_infusionsoft a .inf_power_logo:after{} .inf_infusionsoft_infusionbar_form .inf_infusionsoft_submit_subscription, .inf_infusionsoft_infusionbar_form .inf_infusionsoft_redirect_page, .inf_infusionsoft_preview_infusionbar  .inf_infusionsoft_submit_subscription, .inf_infusionsoft_preview_infusionbar .inf_infusionsoft_redirect_page{ height: 35px;} .inf_infusionsoft .inf_infusionsoft_infusionbar_form .inf_infusionsoft_close_button, .inf_infusionsoft_preview_infusionbar .inf_infusionsoft_close_button (top: 15% !important;)
		';
		$custom_css .= isset($single_optin['form_button_color']) && '' !== $single_optin['form_button_color'] && isset($single_optin['display_as_link']) && $single_optin['display_as_link'] != 1 ? $form_class . ' .inf_infusionsoft_infusionbar_form_content button { background-color: ' . $single_optin['form_button_color'] . ' !important; } ' : '';
		$custom_css .= isset($single_optin['display_as_link']) && $single_optin['display_as_link'] == 1 && $single_optin['button_text_color'] == 'light' ? '.btnaslink_light {background-color: transparent !important;  border: none !important; cursor: pointer !important;}' : '';
		$custom_css .= isset($single_optin['display_as_link']) && $single_optin['display_as_link'] == 1 && $single_optin['button_text_color'] == 'dark' ? '.btnaslink_dark {background-color: transparent !important;  border: none !important; cursor: pointer !important;}' : '';
		$custom_css .= isset($single_optin['header_font']) ? $font_functions->et_gf_attach_font($single_optin['header_font'], $form_class . ' h2, ' . $form_class . ' h2 span, ' . $form_class . ' h2 strong') : '';
		$custom_css .= isset($single_optin['body_font']) ? $font_functions->et_gf_attach_font($single_optin['body_font'], $form_class . ' p, ' . $form_class . ' p span, ' . $form_class . ' p strong, ' . $form_class . ' form input, ' . $form_class . ' form button span') : '';
		$custom_css .= isset($single_optin['custom_css']) ? ' ' . $single_optin['custom_css'] : '';


		return $custom_css;

	}

	public function register_infusionbar__admin_scripts(){
		wp_enqueue_style('infusion-admin-css',INF_INFUSIONSOFT_PLUGIN_URI . '/includes/ext/infusionsoft_infusionbar/css/admin.css');
		wp_enqueue_style('infusion-front-css',INF_INFUSIONSOFT_PLUGIN_URI . '/includes/ext/infusionsoft_infusionbar/css/style.css');
	}
	public function register_infusionbar_scripts(){
		if(is_admin_bar_showing()){
			$admin_bar = true;
		}else{
			$admin_bar = false;
		}
		$script_locals = array(
			'admin_bar' => $admin_bar
		);

		wp_register_script('infusionbar_js', INF_INFUSIONSOFT_PLUGIN_URI.  '/includes/ext/infusionsoft_infusionbar/js/infusion.js', array( 'jquery' ), '1.0', true);
		wp_localize_script('infusionbar_js', 'infusion', $script_locals);
		wp_enqueue_script('infusionbar_js');
		wp_enqueue_style('infusion-front-css',INF_INFUSIONSOFT_PLUGIN_URI . '/includes/ext/infusionsoft_infusionbar/css/style.css');
	}

	static function generate_preview_popup( $details ) {
		$output = '';
		$output = sprintf(
			'<div class="infusionbar_preview_wrapper">
				<div class="inf_infusionsoft_infusionbar inf_infusionsoft_animated inf_infusionsoft_preview_infusionbar inf_infusionsoft_optin %9$s %10$s">
						<div class="inf_infusionsoft_form_container inf_infusionsoft_animation_fadein inf_infusionsoft_infusionbar_container%1$s%2$s%6$s">
						%7$s
						%8$s
					</div>
				</div>
			</div>',
			'bottom' !== $details['form_orientation'] && 'custom_html' !== $details['email_provider'] && 'widget' !== $details['optin_type']
				? sprintf( ' inf_infusionsoft_form_%1$s', esc_attr( $details['form_orientation'] ) )
				: ' inf_infusionsoft_form_bottom',
			'basic_edge' == $details['edge_style'] || '' == $details['edge_style']
				? ''
				: sprintf( ' with_edge %1$s', esc_attr( $details['edge_style'] ) ),
			( 'no_border' !== $details['border_orientation'] )
				? sprintf(
				' inf_infusionsoft_with_border inf_infusionsoft_border_%1$s%2$s',
				esc_attr( $details['border_style'] ),
				esc_attr( ' inf_infusionsoft_border_position_' . $details['border_orientation'] )
			)
				: '',
			( 'rounded' == $details['corner_style'] ) ? ' inf_infusionsoft_rounded_corners' : '',
			( 'rounded' == $details['field_corner'] ) ? ' inf_infusionsoft_rounded' : '',
			'light' == $details['text_color'] ? ' inf_infusionsoft_form_text_light' : ' inf_infusionsoft_form_text_dark',
			self::generate_form_content( 0, 0, '', $details ),
			'',
			esc_attr($details['infusionbar_position']),
			( 'no_border' !== $details['border_orientation'] )
				? sprintf(
				' inf_infusionsoft_border_%1$s%2$s',
				esc_attr( $details['border_style'] ),
				'full' !== $details['border_orientation']
					? ' inf_infusionsoft_border_position_' . $details['border_orientation']
					: ''
			)
				: ''
		);

		return $output;
	}
}

$infusionbar = new infusionsoft_infusionbar();
