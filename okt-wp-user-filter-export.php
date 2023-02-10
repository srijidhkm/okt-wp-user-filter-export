<?php
/**
 * Plugin Name: User Filter and Export
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

if ( ! defined( 'OKTUFE_VER' ) ) {
	define( 'OKTUFE_VER', '1.0.0' );
}

if ( ! class_exists( 'OKT_User_Table' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . '/includes/OKT_User_Table.php' );
}

class OKT_User_Filter_Export {
	/**
	 * Static property to hold our singleton instance
	 *
	 */
	static $instance = false;

	public $plugin_name = 'User Filter and Export';

	public $error_msg = '';

	/**
	 * This is our constructor
	 *
	 * @return void
	 */
	private function __construct() {
		// back end
		add_action( 'plugins_loaded', array( $this, 'textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_menu', array( $this, 'main_menu' ), 10 );
//		add_action( 'admin_action_oktufe_form_response', array( $this, 'oktufe_filter_form_response' ) );
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return WP_Comment_Notes
	 */

	public static function getInstance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

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
			array( $this, 'oktufe_menu' )
		);
	}


	/**
	 * Error Notice
	 *
	 * @return void
	 */
	public function oktufe_error_notices() {
		?>
        <div class="notice notice-error">
            <p><?php _e( $this->error_msg, 'okt-ufe' ); ?></p>
        </div>
		<?php
	}

	/**
	 * Success Notice
	 *
	 * @return void
	 */
	public function oktufe_success_notices() {
		?>
        <div class="notice notice-success">
            <p><?php _e( 'Operation completed successfully', 'okt-ufe' ); ?></p>
        </div>
		<?php
	}

	/**
	 * Return Resume Taxonomy Terms
	 *
	 * @return int[]|string|string[]|WP_Error|WP_Term[]
	 */
	private function oktufe_get_taxonomy( $name ) {
		return get_terms( array(
			'taxonomy'   => $name,
			'hide_empty' => true
		) );
	}

	/**
	 * Get Resume Locations
	 *
	 * @return string[]
	 */
	private function oktufe_get_locations() {
		$args = array(
			'post_type'      => 'resume',
			'order'          => 'ASC',
			'posts_per_page' => - 1
//			'author'    => 4255
		);

		$locations = [];
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) :
			while ( $the_query->have_posts() ) :
				$the_query->the_post();
				$temp_location = get_post_meta( get_the_ID(), '_candidate_location', true );
				if ( $temp_location ) {
					$loc = explode( ',', $temp_location );
					if ( $loc ) {
						$temp_location = $loc[0];
					}
					if ( ! in_array( $temp_location, $locations ) ) {
						$locations[] = $temp_location;
					}
				}
			endwhile;
			wp_reset_postdata();
		endif;

		return $locations;
	}

	/**
	 * Display Filter Form
	 *
	 * @return String
	 */
	private function oktufe_get_filter_form() {
		$experience                    = $this->oktufe_get_taxonomy( 'resume_experience' );
		$skills                        = $this->oktufe_get_taxonomy( 'resume_skill' );
		$locations                     = $this->oktufe_get_locations();
		$oktufe_user_filter_meta_nonce = wp_create_nonce( 'oktufe_user_filter_form_nonce' );

		$selected_skill      = '';
		$selected_location   = '';
		$selected_experience = '';

		if ( isset( $_GET['user-location'] ) && ! empty ( $_GET['user-location'] ) ) {
			$selected_location = $_GET['user-location'];
		}
		if ( isset( $_GET['user-experience'] ) && ! empty ( $_GET['user-experience'] ) ) {
			$selected_experience = $_GET['user-experience'];
		}
		if ( isset( $_GET['user-skills'] ) && ! empty ( $_GET['user-skills'] ) ) {
			$selected_skill = $_GET['user-skills'];
		}

		ob_start(); ?>
        <form id="oktufe_user_filter_form" action="" method="get"
              novalidate="novalidate">
            <input type="hidden" name="page" value="okt-user-filter-export">
            <!--                    <input type="hidden" name="action" value="oktufe_filter_form_response">-->
            <input type="hidden" name="oktufe_meta_nonce"
                   value="<?php echo $oktufe_user_filter_meta_nonce; ?>"/>
            <table class="form-table" role="presentation">
                <tbody>
                <tr class="user-location-wrap">
                    <th><label for="user-location">Location</label></th>
                    <td>
                        <select name="user-location" class="okt-select" id="user-location">
                            <option value="">Select Location</option>
							<?php foreach ( $locations as $location ): ?>
								<?php if ( $selected_location == $location ): ?>
                                    <option value="<?php echo esc_attr( $location ); ?>"
                                            selected><?php echo $location; ?></option>
								<?php else: ?>
                                    <option value="<?php echo esc_attr( $location ); ?>"><?php echo $location; ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="user-skills">Skills</label></th>
                    <td>
                        <select name="user-skills" class="okt-select" id="user-skills">
                            <option value="">Select Skill</option>
							<?php
							if ( ! empty( $skills ) && ! is_wp_error( $skills ) ):
								foreach ( $skills as $skill ): ?>
									<?php if ( $selected_skill == $skill->term_id ): ?>
                                        <option value="<?php echo esc_attr( $skill->term_id ); ?>"
                                                selected><?php echo $skill->name; ?></option>
									<?php else: ?>
                                        <option value="<?php echo esc_attr( $skill->term_id ); ?>"><?php echo $skill->name; ?></option>
									<?php endif; ?>
								<?php endforeach; endif; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="user-experience">Experience</label></th>
                    <td>
                        <select name="user-experience" class="okt-select" id="user-experience">
                            <option value="">Select Experience</option>
							<?php
							if ( ! empty( $experience ) && ! is_wp_error( $experience ) ):
								foreach ( $experience as $exp ): ?>
									<?php if ( $selected_experience == $exp->term_id ): ?>
                                        <option value="<?php echo esc_attr( $exp->term_id ); ?>"
                                                selected><?php echo $exp->name; ?></option>
									<?php else: ?>
                                        <option value="<?php echo esc_attr( $exp->term_id ); ?>"><?php echo $exp->name; ?></option>
									<?php endif; ?>
								<?php endforeach; endif; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <input type="submit" name="submit" id="submit" class="button button-primary"
                               value="Submit">
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
		<?php return ob_get_clean();
	}

	/**
	 * Menu Page
	 *
	 * @return void
	 */
	public function oktufe_menu() {
		if ( current_user_can( 'edit_users' ) ):
			echo $this->oktufe_get_filter_form();
			if ( isset( $_GET['oktufe_meta_nonce'] ) && wp_verify_nonce( $_GET['oktufe_meta_nonce'], 'oktufe_user_filter_form_nonce' ) ) {
				$user_table = new OKT_User_Table();
				//Fetch, prepare, sort, and filter our data...
				$user_table->prepare_items();
				$user_table->display();
			} else {
				$this->error_msg = 'Invalid nonce';
				add_action( 'admin_notices', array( $this, 'oktufe_error_notices' ) );
			}
//			$this->display_results();



		else: ?>
            <p> <?php __( "You are not authorized to perform this operation.", $this->plugin_name ); ?> </p>
		<?php endif;
	}


	/**
	 * Display Result Table
	 *
	 * @return void
	 */
	private function display_results() {
		if ( isset( $_GET['oktufe_meta_nonce'] ) && wp_verify_nonce( $_GET['oktufe_meta_nonce'], 'oktufe_user_filter_form_nonce' ) ) {
			$this->oktufe_process_filter_form();

		} else {
			$this->error_msg = 'Invalid nonce';
			add_action( 'admin_notices', array( $this, 'oktufe_error_notices' ) );
		}
	}

	/**
	 * Process Filter Form
	 *
	 * @return void
	 */
	public function oktufe_process_filter_form() {
		$args = $this->okt_build_args();
		if ( $args ) {
			$the_query = new WP_Query( $args );
			if ( $the_query->have_posts() ) :
				while ( $the_query->have_posts() ) :
					$the_query->the_post();
					$author_id      = get_post_field( 'post_author', get_the_ID() );
					$user           = get_userdata( $author_id );
					$first_name     = $user->user_firstname;
					$mobile         = $user->user_nicename;
					$email          = $user->user_email;
					$location       = get_post_meta( get_the_ID(), '_candidate_location', true );
					$skills_obj     = get_the_terms( get_the_ID(), 'resume_skill' );
					$skill          = join( ', ', wp_list_pluck( $skills_obj, 'name' ) );
					$experience_obj = get_the_terms( get_the_ID(), 'resume_experience' );
					$experience     = join( ', ', wp_list_pluck( $experience_obj, 'name' ) );
					echo "UserId: " . $author_id . "<br />" .
					     "Name: " . $first_name . "<br />" .
					     "Mobile :" . $mobile . "<br />" .
					     "Email: " . $email . "<br />" .
					     "Location: " . $location . "<br />" .
					     "Skill: " . $skill . "<br />" .
					     "Experience: " . $experience . "<br/>";
					echo "----------------------------<br />";
					// TODO: get _candidate _location
					//TODO: get skill
					//TODO: get experience

				endwhile;
				wp_reset_postdata();
			endif;

		}
	}

	/**
	 * Build Filter Query Arguments
	 *
	 * @return array
	 */
	private function okt_build_args() {
		$args = array(
			'post_type'      => 'resume',
			'order'          => 'ASC',
			'posts_per_page' => 25
		);

		if ( isset( $_GET['user-location'] ) && ! empty ( $_GET['user-location'] ) ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_candidate_location',
					'compare' => 'LIKE',
					'value'   => sanitize_key( $_GET['user-location'] ),
				)
			);
		}

		if ( ( isset( $_GET['user-skills'] ) && ! empty ( $_GET['user-skills'] ) ) ||
		     ( isset( $_GET['user-experience'] ) && ! empty ( $_GET['user-experience'] ) )
		) {
			$args['tax_query'] = array(
				'relation' => 'AND',
			);
			//resume_experience
			//resume_skill
			if ( isset( $_GET['user-skills'] ) && ! empty ( $_GET['user-skills'] ) ) {
				$skill_query = array(
					'taxonomy' => 'resume_skill',
					'field'    => 'term_taxonomy_id',
					'terms'    => [ intval( $_GET['user-skills'] ) ],
					'operator' => 'IN'
				);

				array_push( $args['tax_query'], $skill_query );
			}

			if ( isset( $_GET['user-experience'] ) && ! empty ( $_GET['user-experience'] ) ) {
				$exp_query = array(
					'taxonomy' => 'resume_experience',
					'field'    => 'term_taxonomy_id',
					'terms'    => [ intval( $_GET['user-experience'] ) ],
					'operator' => 'IN'
				);

				array_push( $args['tax_query'], $exp_query );
			}
		}

		return $args;
	}

	/**
	 * Admin styles and scripts
	 *
	 * @return void
	 */

	public function admin_scripts() {
		// original version: OKTUFE_VER, replace time()


		wp_register_style( 'select2css', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', false, '1.0', 'all' );
		wp_register_script( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_style( 'select2css' );
		wp_enqueue_script( 'select2' );

		wp_enqueue_style( 'oktufe-admin-css', plugins_url( 'lib/css/admin.css', __FILE__ ), array(), time(), 'all' );
		wp_enqueue_script( 'oktufe-admin-js', plugins_url( 'lib/js/admin.js', __FILE__ ), array(
			'jquery',
			'select2'
		), time(), true );
	}
}

$OKT_User_Filter_Export = OKT_User_Filter_Export::getInstance();
