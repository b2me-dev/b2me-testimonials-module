<?php

	/* Module Name: B2Me Testimonials Module */

	class B2Me_Testimonials_Module {

		public function __construct() {
			add_action('init', array($this, 'register_testimonials_post_type'));
			add_action('add_meta_boxes', array( $this, 'add_meta_box'));
			add_action('save_post', array( $this, 'save'));
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
	
			?>
				<p><label for="organization_name"><strong>Organization Name</strong></label></p>
				<input type="text" id="organization_name" name="organization_name" value="<?php echo esc_attr( $organization_name ); ?>" size="50" />
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

			update_post_meta( $post_id, 'organization_name', $organization_name );
		}

	}

	new B2Me_Testimonials_Module();