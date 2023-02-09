<?php
/**
* Plugin Name: User Filter and Exort
* Plugin URI: https://wp-hut.com/
* Description: Filter and export users
* Version: 1.0.0
* Requires at least: 5.2
* Author: srijidh
* Author URI: https://wp-hut.com/
* License:  GPL v2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Update URI:        https://wp-hut/user-filter-export-plugin/
* Text Domain:       okt-ufe
* Domain Path:       /languages
**/

if( !defined( 'OKTUFE_VER' ) )
	define( 'OKTUFE_VER', '1.0.0' );


class OKT_User_Filter_Export
{
    /**
	 * Static property to hold our singleton instance
	 *
	 */
	static $instance = false;

	/**
	 * This is our constructor
	 *
	 * @return void
	 */
	private function __construct() {
		// back end
		add_action( 'plugins_loaded', array( $this, 'textdomain'));
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts'));
		add_action('admin_menu', array( $this, 'main_menu'), 10);
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return WP_Comment_Notes
	 */

	 public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

    public function textdomain() {

        load_plugin_textdomain( 'okt-ufe', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    }

	/**
	 * Sub Menu Under Users Menu
	 *
	 * @return void
	 */
	public function main_menu() {
		add_submenu_page( 
			'users.php', 
			'User Filter Export', 
			'User Filter Export',
			'manage_options', 
			'okt-user-filter-export', 
			array($this, 'oktufe_menu') 
		   );
	}

	/**
	 * Menu Page
	 *
	 * @return void
	 */

	public function oktufe_menu() {
		echo "My Admin Page";
	}
    /**
	 * Admin styles and scripts
	 *
	 * @return void
	 */

	public function admin_scripts() {
        // original version: OKTUFE_VER, replace time()
		wp_enqueue_style( 'oktufe-admin-css', plugins_url('lib/css/admin.css', __FILE__), array(), time(), 'all' );
        wp_enqueue_script( 'oktufe-admin-js', plugins_url('lib/js/admin.js', __FILE__), array('jquery'), time(), true );
	}
}

$OKT_User_Filter_Export = OKT_User_Filter_Export::getInstance();
