<h1><?php echo esc_html(WP2HTML_PLUGIN_TITLE) ?></h1>

<div class="wp2html-settings-section">

	<div class="information">
		<div class="row">
			<div class="label"><?php echo esc_html_e( 'Base URL', 'wp2html' ) ?></div>
			<div class="item"><?php echo esc_html( get_option( 'home' ) ) ?></div>
		</div>

		<div class="row">
			<div class="label"><?php echo esc_html_e( 'IP address to be accessed', 'wp2html' ) ?></div>
			<div class="item"><?php echo esc_html( $connect_server ) ?></div>
		</div>
	</div>

	<form action="<?php $this->make_admin_url( 'save' ) ?>" method="post">
	<?php wp_nonce_field( 'save-settings' ); ?>
	<div class="row">
		<div class="label"><?php echo esc_html_e( 'The path to write the HTML', 'wp2html' ) ?></div>
		<div class="item">
			<?php echo esc_html( $base_path ) ?><input type="text" name="path" value="<?php $this->option_value( 'path', '/' ) ?>">
			<div class="error-message"><?php echo esc_html( $this->error_message['path'] ?? '' ) ?></div>
		</div>
	</div>

	<div class="row">
		<div class="label"><label for="absolute_path"><?php echo esc_html_e( 'Convert URL to absolute path', 'wp2html' ) ?></label></div>
		<div class="item">
			<input type="checkbox" value="1" id="absolute_path" name="absolute_path"<?php $this->option_checked( 'absolute_path' ) ?>>
		</div>
	</div>

	<div class="row">
		<div class="label"><?php echo esc_html_e( 'Generate pages', 'wp2html' ) ?></div>
		<div class="item">
		<?php if ( get_option( 'show_on_front' ) === 'posts' ): ?>
				<label><input type="checkbox" value="1" name="frontpage"<?php $this->option_checked( 'frontpage' ) ?>> <?php echo esc_html_e( 'Front page', 'wp2html' ) ?></label><br>
			<?php endif; ?>
			<?php if ( get_option( 'show_on_front' ) === 'page' && get_option( 'page_for_posts' ) != 0 ): ?>
				<label><input type="checkbox" value="1" name="blogpage"<?php $this->option_checked( 'blogpage' ) ?>> <?php echo esc_html_e( 'Blog page', 'wp2html' ) ?></label><br>
			<?php endif; ?>
			<label><input type="checkbox" value="1" name="posts"<?php $this->option_checked( 'posts' ) ?>> <?php echo esc_html_e( 'Posts', 'wp2html' ) ?></label><br>
			<label><input type="checkbox" value="1" name="pages"<?php $this->option_checked( 'pages' ) ?>> <?php echo esc_html_e( 'Pages', 'wp2html' ) ?></label><br>
			<label><input type="checkbox" value="1" name="yearly"<?php $this->option_checked( 'yearly' ) ?>> <?php echo esc_html_e( 'Yearly archive', 'wp2html' ) ?></label><br>
			<label><input type="checkbox" value="1" name="monthly"<?php $this->option_checked( 'monthly' ) ?>> <?php echo esc_html_e( 'Monthly archive', 'wp2html' ) ?></label><br>
			<label><input type="checkbox" value="1" name="daily"<?php $this->option_checked( 'daily' ) ?>> <?php echo esc_html_e( 'Daily archive', 'wp2html' ) ?></label><br>
			<label><input type="checkbox" value="1" name="author"<?php $this->option_checked( 'author' ) ?>> <?php echo esc_html_e( 'Author archive', 'wp2html' ) ?></label><br>
			<label><input type="checkbox" value="1" name="category"<?php $this->option_checked( 'category' ) ?>> <?php echo esc_html_e( 'Category archive', 'wp2html' ) ?></label><br>
			<label><input type="checkbox" value="1" name="tag"<?php $this->option_checked( 'tag' ) ?>> <?php echo esc_html_e( 'Tag archive', 'wp2html' ) ?></label><br>
			-- <?php echo esc_html_e( 'Custom post type archive', 'wp2html' ) ?> --<br>
			<?php foreach ( $this->post_types as $post_type ): ?>
				<label><input type="checkbox" value="1" name="ptype[<?php echo esc_html( $post_type->name ) ?>]"<?php $this->option_checked( array( 'ptype', $post_type->name ) ) ?>> <?php echo esc_html( $post_type->name ) ?> <?php echo esc_html_e( 'archive', 'wp2html' ) ?></label><br>
			<?php endforeach; ?>
			-- <?php echo esc_html_e( 'Custom taxonomy archive', 'wp2html' ) ?> --<br>
			<?php foreach ( $this->taxonomies as $taxonomy ): ?>
				<label><input type="checkbox" value="1" name="tax[<?php echo esc_html( $taxonomy->name ) ?>]"<?php $this->option_checked( array ( 'tax' , $taxonomy->name ) ) ?>> <?php echo esc_html( $taxonomy->name ) ?> <?php echo esc_html_e( 'archive', 'wp2html' ) ?></label><br>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="row">
		<div class="label"><?php echo esc_html_e( '404 Page', 'wp2html' ) ?></div>
		<div class="item">
			<label><input type="checkbox" value="1" name="P404"<?php $this->option_checked( 'P404' ) ?>> <?php echo esc_html_e( '404 Page', 'wp2html' ) ?></label>
			<div><?php echo esc_html_e( 'If the page does not exist, generate the HTML for the 404 page.', 'wp2html' ) ?></div>
		</div>
	</div>

	<div class="row">
		<div class="label"><?php echo esc_html_e( 'Paging', 'wp2html' ) ?></div>
		<div class="item">
			<input type="text" name="paged" value="<?php echo esc_html( $this->options['paged'] ?? 'page/__page__' ) ?>">
			<div><?php echo esc_html_e( 'Replace __page__ with the page number.', 'wp2html' ) ?> ex. page/__page__</div>
		</div>
	</div>

	<div class="row">
		<div class="label"><?php echo esc_html_e( 'Pages not generated', 'wp2html' ) ?></div>
		<div class="item">
			<textarea name="ignore_pages" rows="10"><?php echo esc_html( $this->options['ignore_pages'] ?? '' ) ?></textarea>
			<div><?php echo esc_html_e( 'Enter one page per line.', 'wp2html' ) ?> ex. /foo/bar/</div>
		</div>
	</div>

	<div class="row">
		<div class="label"><?php echo esc_html_e( 'Pages to add', 'wp2html' ) ?></div>
		<div class="item">
			<textarea name="additional_pages" rows="10"><?php echo esc_html( $this->options['additional_pages'] ?? '' ) ?></textarea>
			<div><?php echo esc_html_e( 'Enter one page per line.', 'wp2html' ) ?> ex. /foo/bar/</div>
		</div>
	</div>

	<div class="row">
		<div class="left"><button class="wp2html_button button-primary"><?php echo esc_html_e( 'Save', 'wp2html' ) ?></button></div>
		<div class="right">
			<a href="<?php $this->make_admin_url( 'run' ) ?>" class="wp2html_button button-primary run" onclick="return confirm('<?php echo esc_html_e( 'If you have not saved this options yet, be sure to do so.\\n\\nCan I start generating?', 'wp2html' ) ?>')">
				<?php echo esc_html_e( 'Generate', 'wp2html' ) ?>
			</a>
		</div>
	</div>
	</form>

	<hr>

	<div class="generated-paths">
		<h2><?php echo esc_html_e( 'Generated paths', 'wp2html' ) ?></h2>
		<?php echo nl2br( esc_html( urldecode( implode( PHP_EOL, $generated_paths ) ) ), false ) ?>
	</div>

	<hr>

</div>

