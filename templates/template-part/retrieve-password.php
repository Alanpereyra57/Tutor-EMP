<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

defined( 'ABSPATH' ) || exit;


tutor_alert(null, 'any');


if (tutils()->array_get('reset_key', $_GET) && tutils()->array_get('user_id', $_GET)){
    tutor_load_template('template-part.form-retrieve-password');
}else{
	do_action( 'tutor_before_retrieve_password_form' );
	?>

    <form method="post" class="tutor-ResetPassword lost_reset_password">
		<?php tutor_nonce_field(); ?>
        <input type="hidden" name="tutor_action" value="tutor_retrieve_password">

        <p><?php echo apply_filters( 'tutor_lost_password_message', esc_html__( '¿Perdiste tu contraseña? Por favor ingrese su nombre de usuario o dirección de correo electrónico. Recibirá un enlace para crear una nueva contraseña por correo electrónico.', 'tutor' ) ); ?></p><?php // @codingStandardsIgnoreLine ?>

        <div class="tutor-form-row">
            <div class="tutor-form-col-6">
                <div class="tutor-form-group">
                    <label><?php esc_html_e( 'Nombre de usuario o email', 'tutor' ); ?></label>

                    <input type="text" name="user_login" id="user_login" autocomplete="username">
                </div>
            </div>
        </div>

        <div class="clear"></div>

		<?php do_action( 'tutor_lostpassword_form' ); ?>

        <div class="tutor-form-row">
            <div class="tutor-form-col-6">
                <div class="tutor-form-group">
                    <button type="submit" class="tutor-button tutor-button-primary" value="<?php esc_attr_e( 'Resetear contraseña', 'tutor' ); ?>"><?php
						esc_html_e( 'Resetear contraseña', 'tutor' ); ?></button>
                </div>
            </div>
        </div>

    </form>

	<?php
	do_action( 'tutor_after_retrieve_password_form' );
}
