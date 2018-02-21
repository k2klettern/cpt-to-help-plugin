<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('cptToHelp')) {
	class cptToHelp {

		private $adminPages;

		public function __construct() {
			$this->initHooks();
			$this->adminPages = get_option( 'cpt_help_admin_pages' );
		}

		public function initHooks() {
			add_action( 'init', array( $this, 'helpPostType' ) );
			add_action( 'admin_init', array( $this, 'onLoadPlugin' ));
			add_action( 'admin_init', array( $this, 'loadAllTabs'));
			add_action( 'add_meta_boxes', function () {
				add_meta_box( 'helperOptions', __( 'Select where to Show this Tab', CPTTEXDOMAIN ), array(
					$this,
					'optionsCheckMetaBox'
				), 'helper', 'advance' );
			}, 9 );
			add_action( 'save_post', array( $this, 'saveMetaBox' ) );
			add_action( 'edit_form_after_title', function () {
				global $post, $wp_meta_boxes;
				do_meta_boxes( get_current_screen(), 'advance', $post );
				unset( $wp_meta_boxes[ get_post_type( $post ) ]['advance'] );
			} );

		}

		public function loadAllTabs() {
		    $posts = get_posts(array(
		       'post_type' => 'helper',
                'numberposts' => -1,
                'post_status' => 'publish',
            ));

		    foreach ($posts as $post ) {
		        $inputs = get_post_meta($post->ID, 'help_meta_data', true);
		                if($inputs['pages']) {
			                foreach ( $inputs['pages'] as $page ) {
				                add_action( 'load-' . $page, function () use ( $inputs, $post ) {
					                $screen = get_current_screen();
					                if ( isset( $inputs['cpt'] ) && is_array( $inputs['cpt'] ) ) {
						                foreach ( $inputs['cpt'] as $cpt ) {
							                if ( in_array( $screen->post_type, $inputs['cpt'] ) ) {
								                $screen->add_help_tab( array(
									                'id'      => sanitize_title( $post->post_title ) . rand( 4, 4 ),
									                'title'   => $post->post_title,
									                'content' => '<p>' . $inputs['helptext'] . '</p>',
								                ) );
							                }
						                }
					                } else {
						                $screen->add_help_tab( array(
							                'id'      => sanitize_title( $post->post_title ) . rand( 4, 4 ),
							                'title'   => $post->post_title,
							                'content' => '<p>' . $inputs['helptext'] . '</p>',
						                ) );
					                }
				                } );
			                }
		                }
                    }
        }

		public function helpPostType() {
		    $args = array(
			    'labels'      => array(
				    'name'          => __( 'Help Content', CPTTEXDOMAIN ),
				    'singular_name' => __( 'Help Contents', CPTTEXDOMAIN )
			    ),
			    'public'      => false,
			    'publicly_queryable' => true,
			    'has_archive' => false,
			    'show_ui' => true,
			    'supports'    => array( 'title' ),
			    'menu_icon'   => 'dashicons-sos',
			    'menu_position' => 100,
			    'exclude_from_search' => true,
			    'show_in_nav_menus' => false,
			    'rewrite' => false
		    );
			register_post_type( 'helper', $args);
		}

		static function cptToHelpInstall() {
			add_option( 'cptToHelpInstall', 'goon' );
		}


		public function onLoadPlugin() {

			if ( is_admin() && get_option( 'cptToHelpInstall' ) == 'goon' ) {

				delete_option( 'cptToHelpInstall' );

				add_action( 'wp_after_admin_bar_render', array( $this, 'saveOptions' ) );
			}
		}

		public function saveOptions() {
			$adminpages = array(
				'admin.php',
				'admin-db.php',
				'admin-footer.php',
				'admin-functions.php',
				'admin-header.php',
				'bookmarklet.php',
				'categories.php',
				'cat-js.php',
				'edit.php',
				'edit-comments.php',
				'edit-form-advanced.php',
				'edit-form.php',
				'edit-form-comment.php',
				'edit-form-ajax-cat.php',
				'edit-link-form.php',
				'edit-page-form.php',
				'edit-pages.php',
				'execute-pings.php',
				'import.php',
				'index.php',
				'inline-uploading.php',
				'install-helper.php',
				'install.php',
				'link-add.php',
				'link-categories.php',
				'link-import.php',
				'link-manager.php',
				'link-parse-opml.php',
				'list-manipulation.php',
				'menu-header.php',
				'menu.php',
				'moderation.php',
				'options.php',
				'options-discussion.php',
				'options-general.php',
				'options-head.php',
				'options-misc.php',
				'options-permalink.php',
				'options-reading.php',
				'options-writing.php',
				'page-new.php',
				'plugin-editor.php',
				'plugins.php',
				'post.php',
				'profile-update.php',
				'profile.php',
				'setup-config.php',
				'sidebar.php',
				'templates.php',
				'theme-editor.php',
				'themes.php',
				'update-links.php',
				'upgrade-functions.php',
				'upgrade-schema.php',
				'upgrade.php',
				'user-edit.php',
				'users.php',
				'wp-admin.css',
				'images',
				'import'
			);
			update_option( 'cpt_help_admin_pages', $adminpages );

			$html = '<div class="updated">';
			$html .= '<p>';
			$html .= __( 'Options Saved', CPTTEXDOMAIN );
			$html .= '</p>';
			$html .= '</div><!-- /.updated -->';

			echo $html;
			ob_flush();
		}

		public function optionsCheckMetaBox($post) {
			global $wp_post_types;
			$cpts = array_keys( $wp_post_types );
            $input = get_post_meta($post->ID, 'help_meta_data', true);
			?>
            <table class="widefat">
                <thead>
                <tr>
                    <td>
	                    <?php _e( 'Filter the page or pages to show the help tab (required)', CPTTEXDOMAIN ); ?>
                    </td>
                    <td>
	                    <?php _e('Filter by CPT or CPTs to show the help tab (optional)', CPTTEXDOMAIN); ?>
                    </td>
                </tr>
                </thead>
            <tr>
            <td>
            <p>
                <select multiple size="9" name="input[pages][]" class="widefat" required>
					<?php foreach ( $this->adminPages as $page ) { ?>
                        <option value="<?php echo $page; ?>" <?php echo (isset($input['pages']) && in_array($page, $input['pages'])) ? "selected" : ""; ?>><?php echo $page; ?></option>
					<?php } ?>
                </select>
                <br><sup><?php _e('ctrl-shift to select more than one', CPTTEXDOMAIN); ?></sup>
            </p>
            </td>
            <td>
            <p>
                <select multiple size="9" name="input[cpt][]" class="widefat">
                    <?php foreach ( $cpts as $cpt) { ?>
                        <option value="<?php echo $cpt; ?>" <?php echo (isset($input['cpt']) && in_array($cpt, $input['cpt'])) ? "selected" : ""; ?>><?php echo $cpt; ?></option>
                    <?php } ?>
                </select>
                <br><sup><?php _e('ctrl-shift to select more than one', CPTTEXDOMAIN); ?></sup>
            </p>
            </td>
            </tr>
                <tr>
                    <td colspan="2">
                        <hr>
                        <h4><?php _e('Add some Info', CPTTEXDOMAIN); ?></h4>
                        <?php
                        $settings = array( 'media_buttons' => false, 'teeny' => true, 'textarea_name' => 'input[helptext]' );
                        $content = isset($input['helptext']) ? $input['helptext'] : "";
                        $editor_id = 'helptext';
                        wp_editor( $content, $editor_id, $settings );
                        ?>
                    </td>
                </tr>
            </table>
		<?php }

		public function saveMetaBox( $post_id ) {

			if ( isset( $_POST['input'] ) ) {
				update_post_meta( $post_id, 'help_meta_data', $_POST['input'] );
			}
		}
	}
}