<?php

if ( ! class_exists( 'INF_Dashboard' ) ) {
	require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'infusionsoft.php' );
}

class infusionsoft_infusionsoft extends INF_Infusionsoft {

	public function __contruct() {
		parent::__construct();
		$this->permissionsCheck();
	}

	public function draw_infusionsoft_form( $form_fields, $service, $field_values ) {
		$form_fields .= sprintf( '
					<div class="inf_dashboard_account_row">
						<label for="%1$s">%3$s</label>
						<input type="password" value="%5$s" id="%1$s">%7$s
					</div>
					<div class="inf_dashboard_account_row">
						<label for="%2$s">%4$s</label>
						<input type="text" value="%6$s" id="%2$s">%8$s
					</div>', esc_attr( 'api_key_' . $service ), esc_attr( 'client_id_' . $service ),
			__( 'API Key', 'infusionsoft' ), __( 'Application name', 'infusionsoft' ),
			( '' !== $field_values && isset( $field_values['api_key'] ) ) ? esc_attr( $field_values['api_key'] ) : '',
			( '' !== $field_values && isset( $field_values['client_id'] ) ) ? esc_attr( $field_values['client_id'] ) : '',
			INF_Infusionsoft::generate_hint( sprintf( '<a href="http://help.infusionsoft.com/userguides/get-started/tips-and-tricks/api-key" target="_blank">%1$s</a>',
				__( 'Click here for more information', 'infusionsoft' ) ), false ),
			INF_Infusionsoft::generate_hint( 'The subdomain of your Infusionsoft application.', false ) );

		return $form_fields;
	}

	function sync_optins( $app_id, $api_key, $optins ) {
		if ( ! function_exists( 'curl_init' ) ) {
			return __( 'curl_init is not defined ', 'infusionsoft' );
		}

		if ( empty( $app_id ) || empty( $api_key ) ) {
			return;
		}

		if ( ! class_exists( 'InfusionsoftWordPressSDK' ) ) {
			require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'subscription/infusionsoft/InfusionsoftWordPressSDK.php' );
		}

		try {
			$infusion_app = new InfusionsoftWordPressSDK();
			$infusion_app->cfgCon( $app_id, $api_key, 'throw' );
		} catch ( InfusionsoftWordPressSDKException $e ) {
			$error_message = $e->getMessage();
		}

		if ( empty( $error_message ) && ! empty( $optins ) ) {
			$infusion_app->syncWordPressOptins( $optins );
		}
	}

	function optin_delete( $app_id, $api_key, $optin_id ) {
		if ( ! function_exists( 'curl_init' ) ) {
			return __( 'curl_init is not defined ', 'infusionsoft' );
		}

		if ( empty( $app_id ) || empty( $api_key ) ) {
			return;
		}

		if ( ! class_exists( 'InfusionsoftWordPressSDK' ) ) {
			require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'subscription/infusionsoft/InfusionsoftWordPressSDK.php' );
		}

		try {
			$infusion_app = new InfusionsoftWordPressSDK();
			$infusion_app->cfgCon( $app_id, $api_key, 'throw' );
		} catch ( InfusionsoftWordPressSDKException $e ) {
			$error_message = $e->getMessage();
		}

		if ( empty( $error_message ) ) {
			$infusion_app->deleteWordPressOptin( $optin_id );
		}
	}

	/**
	 * Subscribes to Infusionsoft list. Returns either "success" string or error message.
	 * @return string
	 */
	function subscribe_infusionsoft( $api_key, $app_id, $email, $name = '', $last_name = '', $optin_uniqid ) {
		if ( ! function_exists( 'curl_init' ) ) {
			return __( 'curl_init is not defined ', 'infusionsoft' );
		}
		if ( ! is_email( $email ) ) {
			return 'Email address appears to be invalid';
		}
		if ( ! class_exists( 'InfusionsoftWordPressSDK' ) ) {
			require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'subscription/infusionsoft/InfusionsoftWordPressSDK.php' );
		}

		try {
			$infusion_app = new InfusionsoftWordPressSDK();
			$infusion_app->cfgCon( $app_id, $api_key, 'throw' );
		} catch ( InfusionsoftWordPressSDKException $e ) {
			$error_message = $e->getMessage();
		}


		$contact_details = array(
			'FirstName' => $name,
			'LastName'  => $last_name,
			'Email'     => $email,
		);
		$new_contact_id  = $infusion_app->addWithDupCheck( $contact_details, $checkType = 'Email' );
		$infusion_app->optIn( $contact_details['Email'] );

		$response = $infusion_app->achieveWordPressGoal( $new_contact_id, str_replace( ".", "", $optin_uniqid ) );
		if ( $response ) {
			//contact added
			$error_message = 'success';
		} else {
			//update contact if no $response
			$contact_id      = $this->get_contact_id( $infusion_app, $email );
			$updated_contact = $this->update_contact( $infusion_app, $contact_details, $contact_id );
			if ( $updated_contact ) {
				$error_message = 'success';
			}
		}

		return $error_message;
	}

	function testConnection( $app_id, $api_key ) {
		if ( ! function_exists( 'curl_init' ) ) {
			return __( 'curl_init is not defined ', 'infusionsoft' );
		}

		if ( ! class_exists( 'InfusionsoftWordPressSDK' ) ) {
			require_once( INF_INFUSIONSOFT_PLUGIN_DIR . 'subscription/infusionsoft/InfusionsoftWordPressSDK.php' );
		}

		try {
			$infusion_app = new InfusionsoftWordPressSDK();
			$infusion_app->cfgCon( $app_id, $api_key, 'throw' );
			$infusion_app->dsGetSetting( 'application', 'enabled' );
		} catch ( InfusionsoftWordPressSDKException $e ) {
			$error_message = $e->getMessage();
		}

		if ( empty( $error_message ) ) {
			$error_message = 'success';
		}

		return $error_message;
	}

	protected function get_contact_id( $infusion_app, $email ) {
		$returnFields = array( 'Id' );
		$data         = $infusion_app->findByEmail( $email, $returnFields );

		return $data[0]['Id'];
	}

	protected function update_contact( $infusion_app, $contact_details, $contact_id ) {
		$result = $infusion_app->updateCon( $contact_id, $contact_details );

		return $result;
	}
}