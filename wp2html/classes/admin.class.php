<?php

class wp2html_admin {
	public $mode, $page, $options, $wp2html_cl;

	public function __construct() {
		$this->options = get_option( WP2HTML_OPTION_NAME, array() );
		$this->taxonomies = get_taxonomies( array(
			'_builtin' => false,
			'public' => true,
		), 'objects' );
		$this->post_types = get_post_types( array(
			'public'             => true,
			'publicly_queryable' => true,
			'_builtin'           => false,
			'has_archive'        => true,
		), 'objects' );
		$this->wp2html_cl = new wp2html_create_links( $this->options, $this->post_types, $this->taxonomies );
	}

	public static function add_css() {
		echo "<style>\n";
		include WP2HTML_PLUGIN_DIR . 'assets/admin.css';
		echo "</style>\n";
	}

	public function index() {
		$base_path = apply_filters( 'wp2html_change_base_path', WP2HTML_DOCUMENT_ROOT );
		$connect_server = apply_filters( 'wp2html_connect_server', WP2HTML_CONNECT_SERVER );
		$ignore_pages = $this->text2array( $this->options['ignore_pages'] ?? '' );
		$additional_pages = $this->text2array( $this->options['additional_pages'] ?? '' );
		$generated_paths = $this->wp2html_cl->create_paths_from_database();
		$generated_paths = array_merge( $generated_paths, $additional_pages );
		$generated_paths = array_diff( $generated_paths, $ignore_pages );

		include WP2HTML_PLUGIN_DIR . 'views/view_index.php';
	}

	public function save() {
		$input = filter_input_array( INPUT_POST );
		$generate_path = $this->detoxify_path( $input['path'] );
		$input['path'] = $generate_path == '' ? '/' : $generate_path ;
		$input['ignore_pages'] = $this->detoxify_path( $input['ignore_pages'], true );
		$input['additional_pages'] = $this->detoxify_path( $input['additional_pages'], true );
		$input['paged'] = self::trim( $this->detoxify_path( $input['paged'] ) );
		$input['paged'] = $input['paged'] === '' ? WP2HTML_DEFAULT_PAGED : $input['paged'];

		$input = apply_filters( 'wp2html_save_the_options', $input );
		update_option( WP2HTML_OPTION_NAME, $input );
	}

	public function run() {
		$generated_paths = $this->genarate_all_paths();
		$base_path = apply_filters( 'wp2html_change_base_path', WP2HTML_DOCUMENT_ROOT );
		$path = rtrim( $base_path . $this->options['path'], '/' );
		$home = get_option( 'home' );

		foreach ( $generated_paths as $to_path ) {
			$full_path = $path . $to_path;
			ob_flush();
			flush();
			echo esc_html( urldecode( $full_path ) );
			echo '<br>' . PHP_EOL;
			$this->get_static_html_from_url( $home . $to_path, $full_path );
		}
	}

	public function on_save_post( $post_id, $post ) {
		if ( $post->post_status != 'publish' ) {
			return;
		}

		$generated_paths = $this->genarate_all_paths();

		$base_path = apply_filters( 'wp2html_change_base_path', WP2HTML_DOCUMENT_ROOT );
		$path = rtrim( $base_path . $this->options['path'], '/' );
		$home = get_option( 'home' );

		$permalink = get_permalink( $post_id );
		$target_path = str_replace( $home, '', $permalink );

		if ( in_array( $target_path, $generated_paths ) ) {
			$full_path = $path . $target_path;
			$this->get_static_html_from_url( $permalink, $full_path );
		}
	}

	private function genarate_all_paths() {
		$ignore_pages = $this->text2array( $this->options['ignore_pages'] ?? '' );
		$additional_pages = $this->text2array( $this->options['additional_pages'] ?? '' );
		$generated_paths = $this->wp2html_cl->create_paths_from_database();
		$generated_paths = array_merge( $generated_paths, $additional_pages );
		$generated_paths = array_diff( $generated_paths, $ignore_pages );

		return $generated_paths;
	}


	/**
	 * 
	 * get_static_html_from_url
	 * 
	 * @param string $url
	 * @param string $file_path
	 * @return string
	 * 
	 */
	private function get_static_html_from_url( $url, $file_path ) {
		$host   = parse_url( $url, PHP_URL_HOST );

		$connect_server = apply_filters( 'wp2html_connect_server', WP2HTML_CONNECT_SERVER );
		$user_agent     = apply_filters( 'wp2html_set_user_agent', WP2HTML_USER_AGENT );

		$args = array(
			'user-agent'  => $user_agent,
			'headers'     => array(
				'Host' => $host,
			),
			'sslverify'   => false,
		);

		// 内部からIPアドレスベースでアクセスさせる
		$real_url = preg_replace( '/' . addcslashes( $host, '.' ) . '/', $connect_server, $url );

		// 実際にアクセス
		$response = wp_remote_get( $real_url, $args );

		if ( ( ! isset( $this->options['P404'] ) ) && $response['response']['code'] == '404' ) {
			$html = '';
		}
		else {
			// $html = $response['body'];
			$html = wp_remote_retrieve_body( $response );
		}

		// strip spaces at start of the line.
		$html = preg_replace( '/' . PHP_EOL . '\s+/', PHP_EOL, $html );

		if ( isset( $this->options['absolute_path'] ) ) {
			$home = get_option( 'home' );
			// homeだけのURLは/に置き換え
			$html = str_replace( '"' . $home . '"', '/', $html );
			$html = str_replace( $home, '', $html );
		}

		$html = apply_filters( 'wp2html_get_static_html_from_url', $html, $url );

		if ( $html ) {
			// 保存
			$file_path = apply_filters( 'wp2html_change_file_path', $file_path );
			$file_path = urldecode( $file_path );
			@mkdir( $file_path, 0777, true);
			file_put_contents( $file_path . 'index.html', $html );
		}
	}

	/**
	 * make_admin_url
	 * Generate and echo the admin URL.
	 * 
	 * @param option string $mode
	 */
	 public function make_admin_url( $mode = null, $echo = true ) {
		$ret = array( 'page' => $this->page );
		if ( $mode ) {
			$ret['mode'] = $mode;
		}
		$path = 'admin.php?' . http_build_query( $ret );
		if ( $echo ) {
			echo esc_html( $path );
		}
		else {
			return $path;
		}
	}

	public static function trim( $path ) {
		return trim( $path, " \n\r\t\v\0/\\" );
	}

	/**
	 * option_value
	 * Output the value of the options.
	 */
	private function option_value( $item_name, $default = '' ) {
		echo esc_html( isset( $this->options[$item_name] ) ? $this->options[$item_name] : $default );
	}

	/**
	 * option_checked
	 * Output "checked" from the options.
	 * 
	 * @param string $item_name
	 */
	private function option_checked( $item_name ) {

		if ( is_array ( $item_name ) ) {
			echo esc_html( isset( $this->options[$item_name[0]][$item_name[1]] ) ? ' checked' : '' );
		}
		else {
			echo esc_html( isset( $this->options[$item_name] ) ? ' checked' : '' );
		}
	}

	/**
	 * detoxify_path
	 * Removing and replacing illegal characters from the path.
	 * 
	 * @param string $input_path
	 * @param option bool $multiline
	 * @return string
	 */
	private function detoxify_path( $input_path, $multiline = false ) {

		if ( $multiline ) {
			$lines = explode( "\n", $input_path );
			$output = '';
			foreach ( $lines as $line ) {
				$line = trim( $line );
				if ( '' === $line ) {
					continue;
				}
				$path = $this->detoxify_path( $line );
				$output .= $path . PHP_EOL;
			}
			return $output;
		}

		if ( ! empty( $input_path ) ) {
			if ( trim( $input_path ) === '/' ) {
				$path = '/';
			}
			else {
				$path = urldecode( $input_path );
				$path = self::trim( $path );
				$folders = explode( '/', $path );
				$output_folders = array();
				foreach ( $folders as $i => $folder ) {
					if ( '' === $folder ) {
						continue;
					}
					$folder = ( $folder === '.' || $folder === '..' ) ? 'invalid' : $folder;
					array_push( $output_folders, urlencode( $folder ) );
				}
				$path = implode( '/', $output_folders );
				$path = strlen( $path ) > 0 ? '/' . $path . '/' : '';
			}
		}
		else {
			$path = '';
		}

		return apply_filters( 'wp2html_detoxify_path', $path, $input_path, $multiline );
	}

	/**
	 * text2array
	 * 
	 * Make array from multiline text.
	 * 
	 * @param string $text
	 * @return array
	 */
	private function text2array( $text ) {
		$text = trim( $text );
		if ( ! $text ) {
			return array();
		}
		$paths = explode( PHP_EOL, $text );

		$output = array();
		foreach ( $paths as $path ) {
			$path = trim( $path );
			if ( $path ) {
				$output[] = $path;
			}
		}
		return $output;
	}
}
