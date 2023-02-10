<?php
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class OKT_User_Table extends WP_List_Table {
	public $filtered_users = array();

	function __construct(){
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'user',     //singular name of the listed records
			'plural'    => 'users',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
	}

	function column_default($item, $column_name){
		switch($column_name){
//			case 'id':
			case 'name':
			case 'mobile':
			case 'email':
			case 'location':
			case 'skill':
			case 'experience':
				return $item[$column_name];
			default:
				return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}

	function get_columns(){
		$columns = array(
//			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
//			'id'     => 'ID',
			'name'    => 'Name',
			'mobile'  => 'Mobile',
			'email'  => 'Email',
			'location'     => 'Location',
			'skill'    => 'Skill',
			'experience'  => 'Experience',
		);
		return $columns;
	}

	function prepare_items() {
		global $wpdb; //This is used only if making any database queries

		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = 25;


		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();//$this->get_sortable_columns();


		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array($columns, $hidden, $sortable);


		/**
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example
		 * package slightly different than one you might build on your own. In
		 * this example, we'll be using array manipulation to sort and paginate
		 * our data. In a real-world implementation, you will probably want to
		 * use sort and pagination data to build a custom query instead, as you'll
		 * be able to use your precisely-queried data immediately.
		 */
		$data = $this->filtered_users;


		/**
		 * This checks for sorting input and sorts the data in our array accordingly.
		 *
		 * In a real-world situation involving a database, you would probably want
		 * to handle sorting by passing the 'orderby' and 'order' values directly
		 * to a custom query. The returned data will be pre-sorted, and this array
		 * sorting technique would be unnecessary.
		 */
//		function usort_reorder($a,$b){
//			$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
//			$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
//			$result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
//			return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
//		}
//		usort($data, 'usort_reorder');


		/***********************************************************************
		 * ---------------------------------------------------------------------
		 * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
		 *
		 * In a real-world situation, this is where you would place your query.
		 *
		 * For information on making queries in WordPress, see this Codex entry:
		 * http://codex.wordpress.org/Class_Reference/wpdb
		 *
		 * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
		 * ---------------------------------------------------------------------
		 **********************************************************************/
		$args = $this->okt_build_args($per_page);
//		echo "<pre>";
//		var_dump($args);
//		echo "</pre>";
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
					$temp = array(
//						'id' => $author_id,
						'name' => $first_name,
						'mobile' => $mobile,
						'email' => $email,
						'location' => $location,
						'skill' => $skill,
						'experience' => $experience
					);
					array_push($this->filtered_users, $temp);
				endwhile;
				wp_reset_postdata();
			endif;

		}
		$data = $this->filtered_users;
		$args2 = $this->okt_build_args(-1);
		$the_query2 = new WP_Query( $args2 );
		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();

		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		$total_items = $the_query2->post_count;//count($data);


		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to
		 */
//		$data = array_slice($data,(($current_page-1)*$per_page),$per_page);



		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;


		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
		) );
	}

	/**
	 * Build Filter Query Arguments
	 *
	 * @return array
	 */
	private function okt_build_args($per_page = 25) {
		$paged = ($_GET['paged']) ? intval($_GET['paged']) : 1;
		$args = array(
			'post_type'      => 'resume',
			'order'          => 'ASC',
			'paged' => $paged,
			'posts_per_page' => $per_page
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
}
