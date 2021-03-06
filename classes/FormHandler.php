<?php
/**
 * FormHandler class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.1.4.3
 */

namespace TUTOR;


if ( ! defined( 'ABSPATH' ) )
	exit;


class FormHandler {

	public function __construct() {
		add_action('tutor_action_tutor_user_login', array($this, 'process_login'));
		add_action('tutor_action_tutor_retrieve_password', array($this, 'tutor_retrieve_password'));
		add_action('tutor_action_tutor_process_reset_password', array($this, 'tutor_process_reset_password'));

		add_action( 'tutor_reset_password_notification', array( $this, 'reset_password_notification' ), 10, 2 );
		add_filter( 'tutor_lostpassword_url', array( $this, 'lostpassword_url' ) );
	}

	public function process_login(){
		tutils()->checking_nonce();


		$username = tutils()->array_get('log', $_POST);
		$password = tutils()->array_get('pwd', $_POST);


		try {
			$creds = array(
				'user_login'    => trim( wp_unslash( $username ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'user_password' => $password, // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				'remember'      => isset( $_POST['rememberme'] ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			);


			$validation_error = new \WP_Error();
			$validation_error = apply_filters( 'tutor_process_login_errors', $validation_error, $creds['user_login'], $creds['user_password'] );

			if ( $validation_error->get_error_code() ) {
				throw new \Exception( '<strong>' . __( 'Error:', 'tutor' ) . '</strong> ' . $validation_error->get_error_message() );
			}

			if ( empty( $creds['user_login'] ) ) {
				throw new \Exception( '<strong>' . __( 'Error:', 'tutor' ) . '</strong> ' . __( 'Nombre de usuario es requerido.', 'tutor' ) );
			}

			// On multisite, ensure user exists on current site, if not add them before allowing login.
			if ( is_multisite() ) {
				$user_data = get_user_by( is_email( $creds['user_login'] ) ? 'email' : 'login', $creds['user_login'] );

				if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
					add_user_to_blog( get_current_blog_id(), $user_data->ID, 'customer' );
				}
			}

			// Perform the login.
			$user = wp_signon( apply_filters( 'tutor_login_credentials', $creds ), is_ssl() );

			if ( is_wp_error( $user ) ) {
				$message = $user->get_error_message();
				$message = str_replace( '<strong>' . esc_html( $creds['user_login'] ) . '</strong>', '<strong>' . esc_html( $creds['user_login'] ) . '</strong>', $message );
				throw new \Exception( $message );
			} else {
				tutor_redirect_back(apply_filters('tutor_login_redirect_url', tutils()->tutor_dashboard_url()));
			}
		} catch ( \Exception $e ) {
			tutor_flash_set('warning', apply_filters( 'login_errors', $e->getMessage()) );
			do_action( 'tutor_login_failed' );
		}



	}





	public function tutor_retrieve_password(){
		tutils()->checking_nonce();

		//echo '<pre>';
		//die(print_r($_POST));

		$login = sanitize_user( tutils()->array_get('user_login', $_POST));

		if ( empty( $login ) ) {
			tutor_flash_set('danger', __( 'Ingresar un nombre de usuario o un email.', 'tutor' ));
			return false;
		} else {
			// Check on username first, as customers can use emails as usernames.
			$user_data = get_user_by( 'login', $login );
		}

		// If no user found, check if it login is email and lookup user based on email.
		if ( ! $user_data && is_email( $login ) && apply_filters( 'tutor_get_username_from_email', true ) ) {
			$user_data = get_user_by( 'email', $login );
		}

		$errors = new \WP_Error();

		do_action( 'lostpassword_post', $errors );

		if ( $errors->get_error_code() ) {
			tutor_flash_set('danger', $errors->get_error_message() );
			return false;
		}

		if ( ! $user_data ) {
			tutor_flash_set('danger', __( 'Nombre de usuario o email inválidos.', 'tutor' ) );
			return false;
		}

		if ( is_multisite() && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
			tutor_flash_set('danger', __( 'Nombre de usuario o email inválidos.', 'tutor' ) );
			return false;
		}

		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;

		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

		if ( ! $allow ) {
			tutor_flash_set('danger', __( 'No esta permitido resetear la contraseña en este usuario', 'tutor' ) );
			return false;
		} elseif ( is_wp_error( $allow ) ) {
			tutor_flash_set('danger', $allow->get_error_message() );
			return false;
		}

		// Get password reset key (function introduced in WordPress 4.4).
		$key = get_password_reset_key($user_data);

		// Send email notification.
		do_action( 'tutor_reset_password_notification', $user_login, $key );
	}


	public function reset_password_notification( $user_login = '', $reset_key = ''){
		$this->sendNotification($user_login, $reset_key);

		$html = "<h3>".__('Chequea tu email', 'tutor')."</h3>";
		$html .= "<p>".__("Hemos enviado un correo electrónico a la dirección de correo electrónico de esta cuenta. Haga clic en el enlace del correo electrónico para restablecer su contraseña", 'tutor')."</p>";
		$html .= "<p>".__("Si no ve el correo electrónico, verifique otros lugares donde podría estar, como su basura, correo no deseado, redes sociales, promoción u otras carpetas.", 'tutor')."</p>";
		tutor_flash_set('success', $html);
	}

	public function lostpassword_url($url){
		return tutils()->tutor_dashboard_url('retrieve-password');
	}

	public function tutor_process_reset_password(){
		tutils()->checking_nonce();

		$reset_key = sanitize_text_field(tutils()->array_get('reset_key', $_POST));
		$user_id = (int) sanitize_text_field(tutils()->array_get('user_id', $_POST));
		$password = sanitize_text_field(tutils()->array_get('password', $_POST));
		$confirm_password = sanitize_text_field(tutils()->array_get('confirm_password', $_POST));

		$user = get_user_by('ID', $user_id);
		$user = check_password_reset_key( $reset_key, $user->user_login );

		if ( is_wp_error( $user ) ) {
			tutor_flash_set('danger', __( 'Esta clave no es válida o ya se ha utilizado. Restablezca su contraseña nuevamente si es necesario.', 'tutor') );
			return false;
		}


		if ( $user instanceof \WP_User ) {
			if ( !$password ) {
				tutor_flash_set('danger', __( 'Por favor ingresar su contraseña.', 'tutor') );
				return false;
			}

			if ( $password !== $confirm_password) {
				tutor_flash_set('danger', __( 'La contraseña no coincide.', 'tutor') );
				return false;
			}

			tutils()->reset_password($user, $password);

			do_action( 'tutor_user_reset_password', $user );

			// Perform the login.
			$creds = array('user_login' => $user->user_login, 'user_password' => $password, 'remember' => true);
			$user = wp_signon( apply_filters( 'tutor_login_credentials', $creds ), is_ssl() );

			do_action( 'tutor_user_reset_password_login', $user );

			wp_safe_redirect( tutils()->tutor_dashboard_url() );
			exit;
		}
	}

	/**
	 * @param $user_login
	 * @param $reset_key
	 *
	 * Send E-Mail notification
	 * We are sending directly right now, later we will introduce centralised E-Mail notification System...
	 */
	public function sendNotification($user_login, $reset_key){
		//Send the E-Mail to user

		$user_data = get_user_by( 'login', $user_login );

		$variable = array(
			'user_login' => $user_login,
			'reset_key' => $reset_key,
			'user_id' => $user_data->ID,
		);

		$html = tutor_get_template_html('email.send-reset-password', $variable);
		$subject = sprintf(__( 'Contraseña reseteada por %s', 'tutor' ), get_option( 'blogname' ));

		$header = 'Content-Type: text/html' . "\r\n";

		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );

		wp_mail($user_data->user_email, $subject, $html, $header);

		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
	}

	public function get_from_address(){
		return apply_filters('tutor_email_from_address', get_tutor_option('email_from_address'));
	}

	public function get_from_name(){
		return apply_filters('tutor_email_from_name', get_tutor_option('email_from_name'));
	}


}
