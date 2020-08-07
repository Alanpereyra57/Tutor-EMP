<?php
/*
Plugin Name: Tutor EMP
Plugin URI: https://empralidad.com.ar/tutor-emp
Description: Tutor EMP es un fork del plugin Tutor LMS hecho por Themeum y mejorado a una versión por Empralidad,
Author: Empralidad
Version: 1.7.4
Author URI: https://empralidad.com.ar
Requires at least: 5.2
Tested up to: 5.4.2
License: GPLv2 or later
Text Domain: tutor
*/
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defined the tutor main file
 */
define('TUTOR_VERSION', '1.7.1');
define('TUTOR_FILE', __FILE__);
define('TUTOR_PRO_VERSION', __FILE__);

/**
 * Load tutor text domain for translation
 */
add_action( 'init', 'tutor_language_load' );
function tutor_language_load(){
	load_plugin_textdomain( 'tutor', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

/**
 * Tutor Helper function
 *
 * @since v.1.0.0
 */

if ( ! function_exists('tutor')) {
	function tutor() {
		$path = plugin_dir_path( TUTOR_FILE );
		$hasPro = defined('TUTOR_PRO_VERSION');

		$info = array(
			'path'                  => $path,
			'url'                   => plugin_dir_url( TUTOR_FILE ),
			'basename'              => plugin_basename( TUTOR_FILE ),
			'version'               => TUTOR_VERSION,
			'nonce_action'          => 'tutor_nonce_action',
			'nonce'                 => '_wpnonce',
			'course_post_type'      => apply_filters( 'tutor_course_post_type', 'courses' ),
			'lesson_post_type'      => apply_filters( 'tutor_lesson_post_type', 'lesson' ),
			'instructor_role'       => apply_filters( 'tutor_instructor_role', 'tutor_instructor' ),
			'instructor_role_name'  => apply_filters( 'tutor_instructor_role_name', __( 'Tutor Instructor', 'tutor' ) ),
			'template_path'         => apply_filters( 'tutor_template_path', 'tutor/' ),
			'has_pro'               => apply_filters( 'tutor_has_pro', $hasPro),
		);

		return (object) $info;
	}
}

if ( ! class_exists('Tutor')){
	include_once 'classes/Tutor.php';
}

/**
 * @return \TUTOR\Utils
 *
 * Get all helper functions/methods
 *
 */
if ( ! function_exists('tutor_utils')) {
	function tutor_utils() {
		return new \TUTOR\Utils();
	}
}

/**
 * @return \TUTOR\Utils
 *
 * alis of tutor_utils()
 *
 * @since v.1.3.4
 */

if ( ! function_exists('tutils')){
	function tutils(){
		return tutor_utils();
	}
}

/**
 * @return null|\TUTOR\Tutor
 * Run main instance of the Tutor
 *
 * @since v.1.2.0
 */
if ( ! function_exists('tutor_lms')){
	function tutor_lms(){
		return \TUTOR\Tutor::instance();
	}
}
$GLOBALS['tutor'] = tutor_lms();
