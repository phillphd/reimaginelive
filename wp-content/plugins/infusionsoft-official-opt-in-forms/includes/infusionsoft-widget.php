<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class InfusionsoftWidget extends WP_Widget
{
	function __construct() {
		parent::__construct(
			'Infusionsoft', // Base ID
			__( 'Infusionsoft', 'infusionsoft' ), // Name
			array( 'description' => __( 'Infusionsoft widget, please configure all the settings in Infusionsoft control panel', 'infusionsoft' ) ) // Args
		);
	}

	/*function InfusionsoftWidget(){
		$widget_ops = array( 'description' => __( 'Infusionsoft widget, please configure all the settings in Infusionsoft control panel', 'infusionsoft' ) );
		parent::WP_Widget( false, $name = __( 'Infusionsoft', 'infusionsoft' ), $widget_ops );
	}*/

	/* Displays the Widget in the front-end */
	function widget( $args, $instance ){
		extract($args);

		$title = apply_filters( 'inf_infusionsoft_widget_title', empty( $instance['title'] )
			? ''
			: esc_html( $instance['title'] )
		);

		$optin_id = $instance['optin_id'];

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		echo INF_Infusionsoft::display_widget( $optin_id );

		echo $after_widget;
	}

	/* Saves the settings. */
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['optin_id'] = sanitize_text_field( $new_instance['optin_id'] );

		return $instance;
	}

	/* Creates the form for the widget in the back-end. */
	function form( $instance ){
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => __( 'Subscribe', 'infusionsoft' ), 'optin_id' => 'empty' ) );

		$title = $instance['title'];
		$optin_id_saved = $instance['optin_id'];

		# Title
		printf(
			'<p>
				<label for="%1$s">%2$s: </label>
				<input class="widefat" id="%1$s" name="%4$s" type="text" value="%3$s" />
			</p>',
			esc_attr( $this->get_field_id( 'title' ) ),
			esc_html__( 'Title', 'infusionsoft' ),
			esc_attr( $title ),
			esc_attr( $this->get_field_name( 'title' ) )
		);

		$optins_set = INF_Infusionsoft::widget_optins_list();
		$optins_formatted = '';
		foreach ( $optins_set as $optin_id => $name ) {
			$optins_formatted .= sprintf(
				"<option value='%s' %s>%s</option>",
				esc_attr( $optin_id ),
				selected( $optin_id, $optin_id_saved, false ),
				esc_html( $name )
			);
		}

		printf(
			'<p>
				<label for="%1$s">%2$s: </label>
				<select class="widefat" id="%1$s" name="%4$s">%5$s</select>
			</p>',
			esc_attr( $this->get_field_id( 'optin_id' ) ),
			esc_html__( 'Select Optin', 'infusionsoft' ),
			esc_attr( $title ),
			esc_attr( $this->get_field_name( 'optin_id' ) ),
			$optins_formatted
		);
	}
}