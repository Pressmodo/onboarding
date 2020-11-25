<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Helper methods
 *
 * @package   pressmodo-onboarding
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

namespace Pressmodo\Onboarding;

use WP_Error;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Helper methods.
 */
class Helper {

	/**
	 * Convert php array to js object.
	 *
	 * @param string $object_name name of the object
	 * @param array  $l10n data to print
	 * @return array
	 */
	public static function localizeScripts( $object_name, $l10n ) {

		if ( is_array( $l10n ) && isset( $l10n['l10n_print_after'] ) ) { // back compat, preserve the code in 'l10n_print_after' if present.
			$after = $l10n['l10n_print_after'];
			unset( $l10n['l10n_print_after'] );
		}

		foreach ( (array) $l10n as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}

			$l10n[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
		}

		$script = "var $object_name = " . wp_json_encode( $l10n ) . ';';

		if ( ! empty( $after ) ) {
			$script .= "\n$after;";
		}

		return $script;

	}

	/**
	 * Get theme documentation url.
	 *
	 * @return string
	 */
	public static function getDocumentationUrl() {

		/**
		 * Filter: Allow developers to modify the documentation url of the theme.
		 *
		 * @param string $url
		 * @return string
		 */
		return apply_filters( 'pressmodo_theme_documentation_url', 'https://docs.pressmodo.com' );

	}

	/**
	 * Prepares files for upload by standardizing them into an array. This adds support for multiple file upload fields.
	 *
	 * @param array $fileData
	 * @return array
	 */
	public static function prepareUploadedFiles( $fileData ) {

		$files = [];

		if ( is_array( $fileData['name'] ) ) {
			foreach ( $fileData['name'] as $fileDataKey => $fileDataValue ) {
				if ( $fileData['name'][ $fileDataKey ] ) {
					$type    = wp_check_filetype( $fileData['name'][ $fileDataKey ] ); // Map mime type to one WordPress recognises.
					$files[] = [
						'name'     => $fileData['name'][ $fileDataKey ],
						'type'     => $type['type'],
						'tmp_name' => $fileData['tmp_name'][ $fileDataKey ],
						'error'    => $fileData['error'][ $fileDataKey ],
						'size'     => $fileData['size'][ $fileDataKey ],
					];
				}
			}
		} else {
			$type             = wp_check_filetype( $fileData['name'] ); // Map mime type to one WordPress recognises.
			$fileData['type'] = $type['type'];
			$files[]          = $fileData;
		}

		/**
		 * Filter: allow developers to modify the array configured in preparation of files for the upload
		 *
		 * @param array $files
		 * @return array
		 */
		return apply_filters( 'pm_onboarding_prepare_uploaded_files', $files );

	}

	/**
	 * Upload a file.
	 *
	 * @param array $file file details
	 * @param array $args config
	 * @return mixed
	 */
	public static function uploadFile( $file, $args = [] ) {

		global $pmOnboardingUpload, $pmOnboardingUploadingFile;

		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/media.php';

		$args = wp_parse_args(
			$args,
			[
				'file_key'           => '',
				'file_label'         => '',
				'allowed_mime_types' => '',
			]
		);

		$pmOnboardingUpload        = true;
		$pmOnboardingUploadingFile = $args['file_key'];
		$uploadedFile              = new \stdClass();
		if ( '' === $args['allowed_mime_types'] ) {
			$allowedMimeTypes = self::getAllowedMimeTypes();
		} else {
			$allowedMimeTypes = $args['allowed_mime_types'];
		}

		/**
		 * Filter file configuration before upload
		 *
		 * This filter can be used to modify the file arguments before being uploaded, or return a WP_Error
		 * object to prevent the file from being uploaded, and return the error.
		 *
		 * @param array $file               Array of $_FILE data to upload.
		 * @param array $args               Optional file arguments.
		 * @param array $allowedMimeTypes Array of allowed mime types from field config or defaults.
		 */
		$file = apply_filters( 'pm_onboarding_file_pre_upload', $file, $args, $allowedMimeTypes );

		if ( is_wp_error( $file ) ) {
			return $file;
		}

		if ( ! in_array( $file['type'], $allowedMimeTypes, true ) ) {
			// Replace pipe separating similar extensions (e.g. jpeg|jpg) to comma to match the list separator.
			$allowedFileExtensions = implode( ', ', str_replace( '|', ', ', array_keys( $allowedMimeTypes ) ) );

			if ( $args['file_label'] ) {
				// translators: %1$s is the file field label; %2$s is the file type; %3$s is the list of allowed file types.
				return new WP_Error( 'upload', sprintf( __( '"%1$s" (filetype %2$s) needs to be one of the following file types: %3$s', 'pressmodo-onboarding' ), $args['file_label'], $file['type'], $allowedFileExtensions ) );
			} else {
				// translators: %s is the list of allowed file types.
				return new WP_Error( 'upload', sprintf( __( 'Uploaded files need to be one of the following file types: %s', 'pressmodo-onboarding' ), $allowedFileExtensions ) );
			}
		} else {
			$upload = wp_handle_upload( $file, apply_filters( 'submit_job_wp_handle_upload_overrides', [ 'test_form' => false ] ) );
			if ( ! empty( $upload['error'] ) ) {
				return new WP_Error( 'upload', $upload['error'] );
			} else {
				$uploadedFile->url       = $upload['url'];
				$uploadedFile->file      = $upload['file'];
				$uploadedFile->name      = basename( $upload['file'] );
				$uploadedFile->type      = $upload['type'];
				$uploadedFile->size      = $file['size'];
				$uploadedFile->extension = substr( strrchr( $uploadedFile->name, '.' ), 1 );
			}
		}

		$pmOnboardingUpload        = false;
		$pmOnboardingUploadingFile = '';

		return $uploadedFile;

	}

	/**
	 * Get list of allowed file types for the upload.
	 *
	 * @return array
	 */
	public static function getAllowedMimeTypes() {

		$allowed_mime_types = [
			'zip' => 'application/zip',
		];

		/**
		 * Mime types to accept in uploaded files.
		 *
		 * Default is image, pdf, and doc(x) files.
		 *
		 * @param array  {
		 *     Array of allowed file extensions and mime types.
		 *     Key is pipe-separated file extensions. Value is mime type.
		 * }
		 */
		return apply_filters( 'pm_onboarding_mime_types', $allowed_mime_types );

	}

}
