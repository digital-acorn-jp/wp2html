<h1><?php echo WP2HTML_PLUGIN_TITLE ?></h1>

<div class="wp2html-settings-section">

	<div class="information">
		<div class="row">
			<div class="label"><?php _e( 'Base URL', WP2HTML_PLUGIN_NAME ) ?></div>
			<div class="item"><?php echo esc_html( get_option( 'home' ) ) ?></div>
		</div>

		<div class="row">
			<div class="label"><?php _e( 'IP address to be accessed', WP2HTML_PLUGIN_NAME ) ?></div>
			<div class="item"><?php echo esc_html( $connect_server ) ?></div>
		</div>
	</div>

	<form action="<?php $this->make_admin_url( 'save' ) ?>" method="post">
	<div class="row">
		<div class="label"><?php _e( 'The path to write the HTML', WP2HTML_PLUGIN_NAME ) ?></div>
		<div class="item">
			<?php echo esc_html( $base_path ) ?><input type="text" name="path" value="<?php $this->option_value( 'path', '/' ) ?>">
			<div class="error-message"><?php echo esc_html( $this->error_message['path'] ?? '' ) ?></div>
		</div>
	</div>

	<div class="row">
		<div class="label"><label for="absolute_path"><?php _e( 'Convert URL to absolute path', WP2HTML_PLUGIN_NAME ) ?></label></div>
		<div class="item">
			<input type="checkbox" value="1" id="absolute_path" name="absolute_path"<?php $this->option_checked( 'absolute_path' ) ?>>
		</div>
	</div>

	<div class="row">
		<div class="label"><?php _e( 'Generate pages', WP2HTML_PLUGIN_NAME ) ?></div>
		<div class="item">
		<?php if ( get_option( 'show_on_front' ) === 'posts' ): ?>
				<label><input type="checkbox" value="1" name="frontpage"<?php $this->option_checked( 'frontpage' ) ?>> <?php _e( 'Front page', WP2HTML_PLUGIN_NAME ) ?></label><br>
			<?php endif; ?>
			<?php if ( get_option( 'show_on_front' ) === 'page' && get_option( 'page_for_posts' ) != 0 ): ?>
				<label><input type="checkbox" value="1" name="blogpage"<?php $this->option_checked( 'blogpage' ) ?>> <?php _e( 'Blog page', WP2HTML_PLUGIN_NAME ) ?></label><br>
			<?php endif; ?>
			<label><input type="checkbox" value="1" name="posts"<?php $this->option_checked( 'posts' ) ?>> <?php _e( 'Posts', WP2HTML_PLUGIN_NAME ) ?></label><br>
			<label><input type="checkbox" value="1" name="pages"<?php $this->option_checked( 'pages' ) ?>> <?php _e( 'Pages', WP2HTML_PLUGIN_NAME ) ?></label><br>
			<label><input type="checkbox" value="1" name="yearly"<?php $this->option_checked( 'yearly' ) ?>> <?php _e( 'Yearly archive', WP2HTML_PLUGIN_NAME ) ?></label><br>
			<label><input type="checkbox" value="1" name="monthly"<?php $this->option_checked( 'monthly' ) ?>> <?php _e( 'Monthly archive', WP2HTML_PLUGIN_NAME ) ?></label><br>
			<label><input type="checkbox" value="1" name="daily"<?php $this->option_checked( 'daily' ) ?>> <?php _e( 'Daily archive', WP2HTML_PLUGIN_NAME ) ?></label><br>
			<label><input type="checkbox" value="1" name="author"<?php $this->option_checked( 'author' ) ?>> <?php _e( 'Author archive', WP2HTML_PLUGIN_NAME ) ?></label><br>
			<label><input type="checkbox" value="1" name="category"<?php $this->option_checked( 'category' ) ?>> <?php _e( 'Category archive', WP2HTML_PLUGIN_NAME ) ?></label><br>
			<label><input type="checkbox" value="1" name="tag"<?php $this->option_checked( 'tag' ) ?>> <?php _e( 'Tag archive', WP2HTML_PLUGIN_NAME ) ?></label><br>
			-- <?php _e( 'Custom post type archive', WP2HTML_PLUGIN_NAME ) ?> --<br>
			<?php foreach ( $this->post_types as $post_type ): ?>
				<label><input type="checkbox" value="1" name="ptype[<?php echo esc_html( $post_type->name ) ?>]"<?php $this->option_checked( array( 'ptype', $post_type->name ) ) ?>> <?php echo esc_html( $post_type->name ) ?> <?php _e( 'archive', WP2HTML_PLUGIN_NAME ) ?></label><br>
			<?php endforeach; ?>
			-- <?php _e( 'Custom taxonomy archive', WP2HTML_PLUGIN_NAME ) ?> --<br>
			<?php foreach ( $this->taxonomies as $taxonomy ): ?>
				<label><input type="checkbox" value="1" name="tax[<?php echo esc_html( $taxonomy->name ) ?>]"<?php $this->option_checked( array ( 'tax' , $taxonomy->name ) ) ?>> <?php echo esc_html( $taxonomy->name ) ?> <?php _e( 'archive', WP2HTML_PLUGIN_NAME ) ?></label><br>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="row">
		<div class="label"><?php _e( '404 Page', WP2HTML_PLUGIN_NAME ) ?></div>
		<div class="item">
			<label><input type="checkbox" value="1" name="P404"<?php $this->option_checked( 'P404' ) ?>> <?php _e( '404 Page', WP2HTML_PLUGIN_NAME ) ?></label>
			<div><?php _e( 'If the page does not exist, generate the HTML for the 404 page.', WP2HTML_PLUGIN_NAME ) ?></div>
		</div>
	</div>

	<div class="row">
		<div class="label"><?php _e( 'Paging', WP2HTML_PLUGIN_NAME ) ?></div>
		<div class="item">
			<input type="text" name="paged" value="<?php echo esc_html( $this->options['paged'] ?? 'page/__page__' ) ?>">
			<div><?php _e( 'Replace __page__ with the page number.', WP2HTML_PLUGIN_NAME ) ?> ex. page/__page__</div>
		</div>
	</div>

	<div class="row">
		<div class="label"><?php _e( 'Pages not generated', WP2HTML_PLUGIN_NAME ) ?></div>
		<div class="item">
			<textarea name="ignore_pages" rows="10"><?php echo esc_html( $this->options['ignore_pages'] ?? '' ) ?></textarea>
			<div><?php _e( 'Enter one page per line.', WP2HTML_PLUGIN_NAME ) ?> ex. /foo/bar/</div>
		</div>
	</div>

	<div class="row">
		<div class="label"><?php _e( 'Pages to add', WP2HTML_PLUGIN_NAME ) ?></div>
		<div class="item">
			<textarea name="additional_pages" rows="10"><?php echo esc_html( $this->options['additional_pages'] ?? '' ) ?></textarea>
			<div><?php _e( 'Enter one page per line.', WP2HTML_PLUGIN_NAME ) ?> ex. /foo/bar/</div>
		</div>
	</div>

	<div class="row">
		<div class="left"><button class="wp2html_button button-primary"><?php _e( 'Save', WP2HTML_PLUGIN_NAME ) ?></button></div>
		<div class="right">
			<a href="<?php $this->make_admin_url( 'run' ) ?>" class="wp2html_button button-primary run" onclick="return confirm('<?php _e( 'If you have not saved this options yet, be sure to do so.\\n\\nCan I start generating?', WP2HTML_PLUGIN_NAME ) ?>')">
				<?php _e( 'Generate', WP2HTML_PLUGIN_NAME ) ?>
			</a>
		</div>
	</div>
	</form>

	<hr>

	<div class="generated-paths">
		<h2><?php _e( 'Generated paths', WP2HTML_PLUGIN_NAME ) ?></h2>
		<?php echo nl2br( esc_html( urldecode( implode( PHP_EOL, $generated_paths ) ) ), false ) ?>
	</div>

	<hr>

</div>

