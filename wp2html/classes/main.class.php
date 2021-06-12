<?php

class wp2html_main {
	public static function add_menu() {
		add_menu_page(
			WP2HTML_PLUGIN_TITLE,
			strtoupper(WP2HTML_PLUGIN_NAME),
			'export',
			WP2HTML_MENU_SLUG,
			array( get_class(), 'admin_page' ),
			'dashicons-media-code'
		);
	}

	/**
	 * 管理用ページのルーティング
	 */
	public static function admin_page() {
		$page = filter_input( INPUT_GET, 'page' );
		$mode = filter_input( INPUT_GET, 'mode' );

		echo '<div class="wp2html-top">' . PHP_EOL;
		echo '<div class="version">Plugin version. ' . WP2HTML_VERSION . '</div>' . PHP_EOL;;

		if ( defined( 'WP2HTML_ERROR_MESSAGE' ) ) {
			echo '<h1>Error!!</h1>' . PHP_EOL;
			echo '<h2>' . WP2HTML_ERROR_MESSAGE . '</h2>';
		}
		else {
			$ap = new wp2html_admin();
			$ap->mode = $mode;
			$ap->page = $page;
	
			if ( 'save' === $mode ) {
				$ap->save();
				echo '<script>' . PHP_EOL;
				echo 'location.replace("' . $ap->make_admin_url( '', false ) . '");' . PHP_EOL;
				echo '</script>' . PHP_EOL;	
			}
			elseif ( 'run' === $mode ) {
				$ap->run();
				echo '<script>' . PHP_EOL;
				echo 'location.replace("' . $ap->make_admin_url( '', false ) . '");' . PHP_EOL;
				echo '</script>' . PHP_EOL;	
			}
			else {
				$ap->index();
			}
		}
		echo '</div>' . "\n";
	}

	public static function on_save_post( $post_id, $post ) {
		$ap = new wp2html_admin();
		$ap->on_save_post( $post_id, $post);
	}
}
