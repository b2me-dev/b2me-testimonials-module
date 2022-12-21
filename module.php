<?php

	/* Module Name: B2Me Testimonials Module */

	class B2Me_Testimonials_Module {

		public function __construct() {
			add_action( 'wp_enqueue_scripts', array($this, 'b2me_testimonials_module_resources'));
			add_action('init', array($this, 'register_testimonials_post_type'));
			add_action('add_meta_boxes', array( $this, 'add_meta_box'));
			add_action('save_post', array( $this, 'save'));
			add_filter('manage_testimonial_posts_columns', array( $this, 'add_testimonials_columns'));
			add_action('manage_testimonial_posts_custom_column', array( $this, 'display_custom_columns'), 10, 2);
			add_shortcode('b2-testimonials', array($this, 'testimonials'));
		}

		public function b2me_testimonials_module_resources() {
			// Main CSS
			wp_enqueue_style( 'b2me-testimonials-style', get_stylesheet_directory_uri() . '/modules/b2me-testimonials-module/assets/css/style.css');
		
			// Main JS
			wp_enqueue_script( 'b2me-testimonials-scripts', get_stylesheet_directory_uri() . '/modules/b2me-testimonials-module/assets/js/scripts.js', ['jquery']);
		}

		/* Register Testimonials post type */
		public function register_testimonials_post_type() {
			$labels = array(
				'name'                  => 'Testimonials',
				'singular_name'         => 'Testimonial',
				'menu_name'             => 'Testimonials',
				'name_admin_bar'        => 'Testimonial',
				'add_new'               => 'Add New',
				'add_new_item'          => 'Add New Testimonial',
				'new_item'              => 'New Testimonial',
				'edit_item'             => 'Edit Testimonial',
				'view_item'             => 'View Testimonial',
				'all_items'             => 'All Testimonials',
				'search_items'          => 'Search Testimonials',
				'parent_item_colon'     => 'Parent Testimonials:',
				'not_found'             => 'No testimonials found.',
				'not_found_in_trash'    => 'No testimonials found in Trash.',
				'featured_image'        => 'Testimonial Featured Image',
				'set_featured_image'    => 'Set featured image',
				'remove_featured_image' => 'Remove featured image',
				'use_featured_image'    => 'Use as featured image',
				'archives'              => 'Testimonial archives',
				'insert_into_item'      => 'Insert into testimonial',
				'uploaded_to_this_item' => 'Uploaded to this testimonial',
				'filter_items_list'     => 'Filter testimonials list',
				'items_list_navigation' => 'Testimonials list navigation',
				'items_list'            => 'Testimonials list',
			);

			$args = array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'testimonials' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'editor', 'thumbnail' ),
				'menu_icon' 		 => 'dashicons-format-quote',
			);

			register_post_type( 'testimonial', $args );
		}
		
		/* Add meta box for custom fields */
		public function add_meta_box( $post_type ) {
			$post_types = array( 'post', 'testimonial' );
	
			if ( in_array( $post_type, $post_types ) ) {
				add_meta_box(
					'testimonials_custom_metabox',
					'Custom Fields',
					array( $this, 'render_meta_box_content' ),
					$post_type,
					'advanced',
					'high'
				);
			}
		}

		/* Display custom fields */
		public function render_meta_box_content( $post ) {		
			wp_nonce_field( 'b2me_testimonials_inner_custom_box', 'b2me_testimonials_nonce' );
	
			$organization_name = get_post_meta( $post->ID, 'organization_name', true );
			$show_stars = get_post_meta( $post->ID, 'show_stars', true );
			$review_rating = get_post_meta( $post->ID, 'review_rating', true );
	
			?>
				<p><label for="organization_name"><strong>Organization Name</strong></label></p>
				<input type="text" id="organization_name" name="organization_name" value="<?php echo esc_attr( $organization_name ); ?>" size="50" />

				<br/><br/><hr>

				<p><label for="review_rating"><strong>Review Rating (1-5)</strong></label></p>
				<input type="text" id="review_rating" name="review_rating" value="<?php echo esc_attr( $review_rating ); ?>" size="50" />

				<br/><br/><hr>

				<p><label for="show_stars"><strong>Show Rating?</strong></label></p>
				<select name="show_stars" id="show_stars">
					<option value="<?php echo esc_attr( $show_stars ); ?>" selected><?php echo esc_attr( $show_stars ); ?></option>
					<?php
						if ($show_stars == "Yes") {
							echo '<option value="No">No</option>';
						} else {
							if ($show_stars == "") {
								echo '<option value="No">No</option>';
							}
							echo '<option value="Yes">Yes</option>';
						}
					?>
				</select>

			<?php
		}
	
		/* Save and update custom fields */
		public function save( $post_id ) {
			/* Check nonce */
			if ( ! isset( $_POST['b2me_testimonials_nonce'] ) ) {
				return $post_id;
			}

			$nonce = $_POST['b2me_testimonials_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'b2me_testimonials_inner_custom_box' ) ) {
				return $post_id;
			}
	
			/* Do not autosave */
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}
	
			/* Check user privilege */
			if ( 'page' == $_POST['post_type'] ) {
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return $post_id;
				}
			} else {
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return $post_id;
				}
			}
	
			/* Update fields */
			$organization_name = sanitize_text_field( $_POST['organization_name'] );
			$show_stars = sanitize_text_field( $_POST['show_stars'] );
			$review_rating = sanitize_text_field( $_POST['review_rating'] );

			update_post_meta( $post_id, 'organization_name', $organization_name );
			update_post_meta( $post_id, 'show_stars', $show_stars );
			update_post_meta( $post_id, 'review_rating', $review_rating );
		}

		/* Display testimonials columns */
		public function add_testimonials_columns($columns) {
			return array_merge($columns, 
						array(
							'organization_name' => 'Organization',
							'show_stars' => 'Show Rating?',
							'review_rating' => 'Rating',
						)
					);
		}

		function display_custom_columns($column, $post_id) {
			switch ($column) {
				case 'organization_name':
					echo get_post_meta($post_id, 'organization_name', true);
					break;
				case 'show_stars':
					echo get_post_meta($post_id, 'show_stars', true);
					break;
				case 'review_rating':
					echo get_post_meta($post_id, 'review_rating', true);
					break;
			}
		}

		/* Shortcode */
		public function testimonials($attr) {
			// Options
			$attr = shortcode_atts(array(
				'class' => '',
				'limit' => '',
			), $attr);

			// Query
			$query = new WP_Query([
				'post_type' => 'testimonial',
				'posts_per_page' => $attr['limit'],
			]);

			$reviews = '';
			$reviewsCount = 0;
			$reviewsPostCount = $query->post_count; ;
			$reviewsRatingsTotal = 0;
			$schema = '';

			// Get testimonials
			if ( $query->have_posts() ) :
				while ( $query->have_posts() ) :
					$query->the_post();

					$title = get_the_title();
					$featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'full');
					$content = strip_tags(get_the_content());
					$organization_name = get_post_meta( get_the_ID(), 'organization_name', true );
					$review_rating = get_post_meta( get_the_ID(), 'review_rating', true );
					$review_rating_int = (int)$review_rating;
					$show_stars = get_post_meta( get_the_ID(), 'show_stars', true );
					$post_date = get_the_date( 'Y M j' );
					$star_item = '';
					$star_element = '';

					// Construct star by review rating
					for ($i = 0; $i < $review_rating_int; $i++) {
						$star_item .= '<i class="fa-solid fa-star"></i>';
					}

					// Check appearance
					if ($show_stars == 'Yes') {
						$star_element = '<div>'. $star_item . '</div>';
					}

					// Construct review items
					$reviews .= '<li data-aos="fade-up" data-aos-duration=".6" data-aos-once="true">
						<div class="b2-testimonials-item">
							<div class="b2-testimonials-item-col col-img">
								<img src="'. $featured_img_url .'" alt="'. $title .'" class="b2-img-responsive">
							</div>
							<div class="b2-testimonials-item-col col-txt">
								'. $star_element .'
								<p>"'. $content .'"</p>
								<h3>'. $title .'</h3>
								<h4>'. $organization_name .'</h4>
							</div>
						</div>
					</li>';

					// Construct review items schema
					$schema .= '{
						"@type": "Review",
						"author": "'. $title .'",
						"datePublished": "'. $post_date .'",
						"reviewBody": "'. $content .'",
						"name": "'. $title .'",
						"reviewRating": {
							"@type": "Rating",
							"bestRating": "5",
							"ratingValue": "'. $review_rating .'",
							"worstRating": "1"
						}
					}';

					// Add up all ratings
					$reviewsRatingsTotal += $review_rating_int;

					// Monitor current item count
					$reviewsCount++;

					// Add comma on schema if reviews is not yet complete
					if ($reviewsCount < $reviewsPostCount) {
						$schema .= ',';
					}

				endwhile;
			endif;

			// Get Review Average
			$averageRating = $reviewsRatingsTotal / $reviewsCount;
	
			// Construct HTML
			$html = '<ul class="b2-testimonials">
				'. $reviews .'
			</ul>
			<script type="application/ld+json">
				{
					"@context": "https://schema.org",
					"@type": "Product",
					"aggregateRating": {
						"@type": "AggregateRating",
						"ratingValue": "'. $averageRating .'",
						"reviewCount": "'. $reviewsCount .'"
					},
					"description": "",
					"name": "",
					"review": [
						'. $schema .'
					]
				}
			</script>';
	
			return $html;
		}
	}

	new B2Me_Testimonials_Module();