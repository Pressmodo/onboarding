<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Database search and replace.
 *
 * @package   pressmodo-onboarding
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

namespace Pressmodo\Onboarding;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Search the database and replace values.
 */
class SearchReplace {

	/**
	 * The page size used throughout the plugin
	 *
	 * @var int
	 */
	public $pageSize;

	/**
	 * The WordPress database class.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Get things started.
	 */
	public function __construct() {

		global $wpdb;
		$this->wpdb = $wpdb;

		$this->pageSize = $this->getPageSize();
	}

	/**
	 * Get list of demo tables to inspect.
	 *
	 * @return array
	 */
	public static function getTables() {

		global $wpdb;

		$tables = $wpdb->get_col( 'SHOW TABLES' );

		return $tables;
	}

	/**
	 * Get the size of each database table.
	 *
	 * @return array
	 */
	public static function getSizes() {
		global $wpdb;

		$sizes  = array();
		$tables = $wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );

		if ( is_array( $tables ) && ! empty( $tables ) ) {

			foreach ( $tables as $table ) {
				$size                    = round( $table['Data_length'] / 1024 / 1024, 2 );
				$sizes[ $table['Name'] ] = sprintf( __( '(%s MB)', 'better-search-replace' ), $size );
			}
		}

		return $sizes;
	}

	/**
	 * Returns the current page size.
	 *
	 * @return int
	 */
	public function getPageSize() {
		$page_size = get_option( 'bsr_page_size' ) ? get_option( 'bsr_page_size' ) : 20000;
		return absint( $page_size );
	}

	/**
	 * Returns the number of pages in a table.
	 *
	 * @param string $table
	 * @return int
	 */
	public function getPagesInTable( $table ) {
		$table = esc_sql( $table );
		$rows  = $this->wpdb->get_var( "SELECT COUNT(*) FROM `$table`" );
		$pages = ceil( $rows / $this->pageSize );
		return absint( $pages );
	}

	/**
	 * Gets the total number of pages in the DB.
	 *
	 * @param array $tables
	 * @return int
	 */
	public function getTotalPages( $tables ) {
		$total_pages = 0;

		foreach ( $tables as $table ) {

			// Get the number of rows & pages in the table.
			$pages = $this->getPagesInTable( $table );

			// Always include 1 page in case we have to create schemas, etc.
			if ( $pages == 0 ) {
				$pages = 1;
			}

			$total_pages += $pages;
		}

		return absint( $total_pages );
	}

	/**
	 * Get the columns of a table.
	 *
	 * @param string $table
	 * @return array
	 */
	public function getColumns( $table ) {
		$primary_key = null;
		$columns     = array();
		$fields      = $this->wpdb->get_results( 'DESCRIBE ' . $table );

		if ( is_array( $fields ) ) {
			foreach ( $fields as $column ) {
				$columns[] = $column->Field;
				if ( $column->Key == 'PRI' ) {
					$primary_key = $column->Field;
				}
			}
		}

		return array( $primary_key, $columns );
	}

	/**
	 * Do search and replace.
	 *
	 * @param string $table
	 * @param string $page
	 * @param array  $args
	 * @return array
	 */
	public function srdb( $table, $page, $args ) {

		// Load up the default settings for this chunk.
		$table        = esc_sql( $table );
		$current_page = absint( $page );
		$pages        = $this->getPagesInTable( $table );
		$done         = false;

		$args['search_for']   = str_replace( '#BSR_BACKSLASH#', '\\', $args['search_for'] );
		$args['replace_with'] = str_replace( '#BSR_BACKSLASH#', '\\', $args['replace_with'] );

		$table_report = array(
			'change'  => 0,
			'updates' => 0,
			'start'   => microtime( true ),
			'end'     => microtime( true ),
			'errors'  => array(),
			'skipped' => false,
		);

		// Get a list of columns in this table.
		list( $primary_key, $columns ) = $this->getColumns( $table );

		// Bail out early if there isn't a primary key.
		if ( null === $primary_key ) {
			$table_report['skipped'] = true;
			return array(
				'table_complete' => true,
				'table_report'   => $table_report,
			);
		}

		$current_row = 0;
		$start       = $page * $this->pageSize;
		$end         = $this->pageSize;

		// Grab the content of the table.
		$data = $this->wpdb->get_results( "SELECT * FROM `$table` LIMIT $start, $end", ARRAY_A );

		// Loop through the data.
		foreach ( $data as $row ) {
			$current_row++;
			$update_sql = array();
			$where_sql  = array();
			$upd        = false;

			foreach ( $columns as $column ) {

				$data_to_fix = $row[ $column ];

				if ( $column == $primary_key ) {
					$where_sql[] = $column . ' = "' . $this->mysqlEscapeMimic( $data_to_fix ) . '"';
					continue;
				}

				// Skip GUIDs by default.
				if ( 'on' !== $args['replace_guids'] && 'guid' == $column ) {
					continue;
				}

				if ( $this->wpdb->options === $table ) {

					// Skip any BSR options as they may contain the search field.
					if ( isset( $should_skip ) && true === $should_skip ) {
						$should_skip = false;
						continue;
					}

					// If the Site URL needs to be updated, let's do that last.
					if ( isset( $update_later ) && true === $update_later ) {
						$update_later = false;
						$edited_data  = $this->recursiveUnserializeReplace( $args['search_for'], $args['replace_with'], $data_to_fix, false, $args['case_insensitive'] );

						if ( $edited_data != $data_to_fix ) {
							$table_report['change']++;
							$table_report['updates']++;
							update_option( 'bsr_update_site_url', $edited_data );
							continue;
						}
					}

					if ( '_transient_bsr_results' === $data_to_fix || 'bsr_profiles' === $data_to_fix || 'bsr_update_site_url' === $data_to_fix || 'bsr_data' === $data_to_fix ) {
						$should_skip = true;
					}

					if ( 'siteurl' === $data_to_fix && $args['dry_run'] !== 'on' ) {
						$update_later = true;
					}
				}

				// Run a search replace on the data that'll respect the serialisation.
				$edited_data = $this->recursiveUnserializeReplace( $args['search_for'], $args['replace_with'], $data_to_fix, false, $args['case_insensitive'] );

				// Something was changed
				if ( $edited_data != $data_to_fix ) {
					$update_sql[] = $column . ' = "' . $this->mysqlEscapeMimic( $edited_data ) . '"';
					$upd          = true;
					$table_report['change']++;
				}
			}

			// Determine what to do with updates.
			if ( $args['dry_run'] === 'on' ) {
				// Don't do anything if a dry run
			} elseif ( $upd && ! empty( $where_sql ) ) {
				// If there are changes to make, run the query.
				$sql    = 'UPDATE ' . $table . ' SET ' . implode( ', ', $update_sql ) . ' WHERE ' . implode( ' AND ', array_filter( $where_sql ) );
				$result = $this->wpdb->query( $sql );

				if ( ! $result ) {
					$table_report['errors'][] = sprintf( __( 'Error updating row: %d.', 'better-search-replace' ), $current_row );
				} else {
					$table_report['updates']++;
				}
			}
		} // end row loop

		if ( $current_page >= $pages - 1 ) {
			$done = true;
		}

		// Flush the results and return the report.
		$table_report['end'] = microtime( true );
		$this->wpdb->flush();
		return array(
			'table_complete' => $done,
			'table_report'   => $table_report,
		);
	}

	/**
	 * Take a serialised array and unserialise it replacing elements as needed and
	 * unserialising any subordinate arrays and performing the replace on those too.
	 *
	 * @param string  $from
	 * @param string  $to
	 * @param string  $data
	 * @param boolean $serialised
	 * @param boolean $case_insensitive
	 * @return string|array
	 */
	public function recursiveUnserializeReplace( $from = '', $to = '', $data = '', $serialised = false, $case_insensitive = false ) {
		try {

			if ( is_string( $data ) && ! is_serialized_string( $data ) && ( $unserialized = $this->unserialize( $data ) ) !== false ) {
				$data = $this->recursiveUnserializeReplace( $from, $to, $unserialized, true, $case_insensitive );
			} elseif ( is_array( $data ) ) {
				$_tmp = array();
				foreach ( $data as $key => $value ) {
					$_tmp[ $key ] = $this->recursiveUnserializeReplace( $from, $to, $value, false, $case_insensitive );
				}

				$data = $_tmp;
				unset( $_tmp );
			}

			// Submitted by Tina Matter
			elseif ( is_object( $data ) ) {
				// $data_class = get_class( $data );
				$_tmp  = $data; // new $data_class( );
				$props = get_object_vars( $data );
				foreach ( $props as $key => $value ) {
					$_tmp->$key = $this->recursiveUnserializeReplace( $from, $to, $value, false, $case_insensitive );
				}

				$data = $_tmp;
				unset( $_tmp );
			} elseif ( is_serialized_string( $data ) ) {
				if ( $data = $this->unserialize( $data ) !== false ) {
					$data = $this->str_replace( $from, $to, $data, $case_insensitive );
					$data = serialize( $data );
				}
			} else {
				if ( is_string( $data ) ) {
					$data = $this->str_replace( $from, $to, $data, $case_insensitive );
				}
			}

			if ( $serialised ) {
				return serialize( $data );
			}
		} catch ( Exception $error ) {

		}

		return $data;
	}

	/**
	 * Updates the Site URL if necessary
	 *
	 * @return bool
	 */
	public function maybeUpdateSiteUrl() {
		$option = get_option( 'bsr_update_site_url' );

		if ( $option ) {
			update_option( 'siteurl', $option );
			delete_option( 'bsr_update_site_url' );
			return true;
		}

		return false;
	}

	/**
	 * Mimics the mysql_real_escape_string function. Adapted from a post by 'feedr' on php.net.
	 *
	 * @link   http://php.net/manual/en/function.mysql-real-escape-string.php#101248
	 * @param  string $input The string to escape.
	 * @return string
	 */
	public function mysqlEscapeMimic( $input ) {
		if ( is_array( $input ) ) {
			return array_map( __METHOD__, $input );
		}
		if ( ! empty( $input ) && is_string( $input ) ) {
			return str_replace( array( '\\', "\0", "\n", "\r", "'", '"', "\x1a" ), array( '\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z' ), $input );
		}

		return $input;
	}

	/**
	 * Return unserialized object or array
	 *
	 * @param string $serialized_string Serialized string.
	 * @param string $method            The name of the caller method.
	 *
	 * @return mixed, false on failure
	 */
	public static function unserialize( $serialized_string ) {
		if ( ! is_serialized( $serialized_string ) ) {
			return false;
		}

		$serialized_string   = trim( $serialized_string );
		$unserialized_string = @unserialize( $serialized_string );

		return $unserialized_string;
	}

	/**
	 * Wrapper for str_replace
	 *
	 * @param string      $from
	 * @param string      $to
	 * @param string      $data
	 * @param string|bool $case_insensitive
	 *
	 * @return string
	 */
	public function str_replace( $from, $to, $data, $case_insensitive = false ) {
		if ( 'on' === $case_insensitive ) {
			$data = str_ireplace( $from, $to, $data );
		} else {
			$data = str_replace( $from, $to, $data );
		}

		return $data;
	}

}
