<?php

class wp2html_create_links {
	private $options, $post_types, $taxonomies;

	public function __construct( $options, $post_types, $taxonomies ) {
		$this->options    = $options;
		$this->post_types = $post_types;
		$this->taxonomies = $taxonomies;
	}

	/**
	 * remove_home_from_url
	 * Remove the home URL from the input URL.
	 * 
	 * @param string $url
	 * @return string
	 */
	private function remove_home_from_url( $url ) {
		$home = get_option( 'home' );
		$url = str_replace( $home, '', $url );
		return apply_filters( 'wp2html_remove_home_from_url', $url );
	}

	/**
	 * set_page
	 * Generate additional paths for pagination.
	 * 
	 * @param int $page
	 * @return string
	 */
	private function set_page( $page ) {
		$paged = $this->options['paged'] ?? WP2HTML_DEFAULT_PAGED;

		return $page > 1 ? str_replace( '__page__', $page, $paged ) . '/' : '';
	}

	/**
	 * create_parent_directory_path
	 * Generate parent directories from the path.
	 * 
	 * @param int $path
	 * @return array
	 */
	private function create_parent_directory_path( $path ) {
		$path = wp2html_admin::trim( $path );
		$dirs = explode( '/', $path );
		$paths = array();

		for ( $i = 0; $i < count( $dirs ) - 1; $i++ ) {
			$paths[$i] = '/';
			for ( $j = 0; $j <= $i; $j++ ) {
				$paths[$i] .= $dirs[$j] . '/';
			}
		}

		return $paths;
	}

	/**
	 * is_set_option
	 * 
	 * @param string $option_name
	 * @return boolean
	 */
	private function is_set_option( $option_name ) {
		return isset( $this->options[$option_name] );
	}

	/**
	 * get_links
	 * 
	 * Generate the paged links for a path.
	 * The type parameter should be a yearly, monthly, daily, author, post_type, taxonomy.
	 * If the type parameter is post_type or taxonomy, then specify slug.
	 * 
	 * @param string $type
	 * @param string $slug
	 * @return array
	 */
	public function get_archive_links( $type, $slug = '' ) {
		global $wpdb;

		$output = array();

		if ( 'yearly' === $type ) {
			$sql =
				"SELECT COUNT(ID) AS `count`, YEAR(post_date) AS `year` ".
				"FROM {$wpdb->posts} ".
				"WHERE post_type = 'post' AND post_status = 'publish' ".
				"GROUP BY YEAR(post_date) ".
				"ORDER BY post_date";

			$output = array_merge( $output, $this->get_archive_links_from_sql_and_function( $sql, 'get_year_link' ) );
		}
		elseif ( 'monthly' === $type ) {
			$sql =
				"SELECT COUNT(ID) AS `count`, YEAR(post_date) AS `year`, MONTH(post_date) AS `month` ".
				"FROM {$wpdb->posts} ".
				"WHERE post_type = 'post' AND post_status = 'publish' ".
				"GROUP BY YEAR(post_date), MONTH(post_date) ".
				"ORDER BY post_date";

			$output = array_merge( $output, $this->get_archive_links_from_sql_and_function( $sql, 'get_month_link' ) );
		}
		elseif ( 'daily' === $type ) {
			$sql =
				"SELECT COUNT(ID) AS `count`, YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, DAYOFMONTH(post_date) AS `day` ".
				"FROM {$wpdb->posts} ".
				"WHERE post_type = 'post' AND post_status = 'publish' ".
				"GROUP BY YEAR(post_date), MONTH(post_date), DAYOFMONTH(post_date) ".
				"ORDER BY post_date";

			$output = array_merge( $output, $this->get_archive_links_from_sql_and_function( $sql, 'get_day_link' ) );
		}
		elseif ( 'author' === $type ) {
			$sql =
				"SELECT COUNT(ID) AS `count`, post_author ".
				"FROM {$wpdb->posts} ".
				"WHERE post_type = 'post' AND post_status = 'publish' ".
				"GROUP BY post_author ".
				"ORDER BY post_author";

			$output = array_merge( $output, $this->get_archive_links_from_sql_and_function( $sql, 'get_author_posts_url' ) );
		}
		elseif ( 'post_type' === $type && $slug ) {
			$sql = $wpdb->prepare(
				"SELECT COUNT(ID) AS `count` FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'", $slug
			);

			$output = array_merge( $output, $this->get_archive_links_from_sql_and_function( $sql, 'get_post_type_archive_link', $slug ) );
		}
		elseif ( 'category' === $type || 'post_tag' === $type ) {
			$sql = $wpdb->prepare(
				"SELECT tt.`count`, tm.`slug` ".
				"FROM {$wpdb->term_taxonomy} AS tt LEFT JOIN {$wpdb->terms} AS tm ON (tt.term_id = tm.term_id) ".
				"WHERE tt.`taxonomy` = %s AND tt.`count` > 0", $type
			);

			$output = array_merge( $output, $this->get_archive_links_from_sql_and_function( $sql, 'get_term_link', $type ) );
		}
		elseif ( 'taxonomy' === $type ) {
			$sql = $wpdb->prepare(
				"SELECT tt.`count`, tm.`slug` ".
				"FROM {$wpdb->term_taxonomy} AS tt LEFT JOIN {$wpdb->terms} AS tm ON (tt.term_id = tm.term_id) ".
				"WHERE tt.`taxonomy` = %s AND tt.`count` > 0", $slug
			);

			$output = array_merge( $output, $this->get_archive_links_from_sql_and_function( $sql, 'get_term_link', $slug ) );
		}

		return $output;
	}

	/**
	 * get_archive_links_from_sql_and_function
	 * 
	 * Get archive links from SQL and function
	 * 
	 * @param string $sql
	 * @param string $func
	 * @param string $slug
	 * @return array
	 */
	private function get_archive_links_from_sql_and_function( $sql, $func, $slug = '' ) {
		global $wpdb;
		$posts_per_page = get_option( 'posts_per_page' );

		$output = array();

		if ( $sql ) {
			$results = $wpdb->get_results( $sql );
		}

		if ( $results ) {
			foreach ( $results as $result ) {
				if ( 'get_year_link' === $func ) {
					$link = $this->remove_home_from_url( $func( $result->year ) );
				}
				elseif ( 'get_month_link' === $func ) {
					$link = $this->remove_home_from_url( $func( $result->year, $result->month ) );
				}
				elseif ( 'get_day_link' === $func ) {
					$link = $this->remove_home_from_url( $func( $result->year, $result->month, $result->day ) );
				}
				elseif ( 'get_author_posts_url' === $func ) {
					$link = $this->remove_home_from_url( $func( $result->post_author ) );
				}
				elseif ( 'get_post_type_archive_link' === $func ) {
					$link = $this->remove_home_from_url( $func( $slug ) );
				}
				elseif ( 'get_term_link' === $func ) {
					$link = $this->remove_home_from_url( $func( $result->slug, $slug ) );
					$output = array_merge( $output, $this->create_parent_directory_path( $link ) );
				}
				else {
					return array();
				}

				$max_num_pages = ceil( $result->count / $posts_per_page );
				if ( $max_num_pages > 1 ) {
					$output = array_merge( $output, $this->create_parent_directory_path( $link . $this->set_page( 2 ) ) );
				}
				for ( $i = 1; $i <= $max_num_pages; $i++ ) {
					$output[] = $link . $this->set_page( $i );
				}
			}
		}
		$output = array_unique( $output );
		return $output;
	}

	/**
	 * create_paths_from_database
	 * Generates an array of paths depending on the options.
	 * 
	 * @return array
	 */
	public function create_paths_from_database() {
		$paths = array();

		// Front page
		$paths[] = '/';
		// Paged front page 
		if ( get_option( 'show_on_front' ) === 'posts' && isset( $this->options['frontpage'] ) ||
			get_option( 'page_for_posts' ) != 0 && isset( $this->options['blogpage'] ))  {
			
			$the_query = new WP_Query( array( 'post_type' => 'post' ) );
			$max = $the_query->max_num_pages;

			$base_path = '/';
			if ( get_option( 'page_for_posts' ) != 0 ) {
				$pages = get_pages( array( 'include' => get_option('page_for_posts') ) );
				if ( $pages ) {
					$base_path .= $pages[0]->post_name . '/';
				}
			}
			if ( $max > 1 ) {
				$paths = array_merge( $paths, $this->create_parent_directory_path( $base_path . $this->set_page( 2 ) ) );
				for ( $i = 2; $i <= $max; $i++ ) {
					$path = $base_path . $this->set_page( $i );
					$paths[] = $path;
				}
			}
			wp_reset_postdata();
		}
		// Yearly archive page
		if ( $this->is_set_option( 'yearly' ) ) {
			$paths = array_merge( $paths, $this->get_archive_links( 'yearly' ) );
		}
		// Monthly archive page
		if ( $this->is_set_option( 'monthly' ) ) {
			$paths = array_merge( $paths, $this->get_archive_links( 'monthly' ) );
		}
		// Daily archive page
		if ( $this->is_set_option( 'daily' ) ) {
			$paths = array_merge( $paths, $this->get_archive_links( 'daily' ) );
		}
		// Author archive page
		if ( $this->is_set_option( 'author' ) ) {
			$paths = array_merge( $paths, $this->get_archive_links( 'author' ) );
		}
		// Category archive page
		if ( $this->is_set_option( 'category' ) ) {
			$paths = array_merge( $paths, $this->get_archive_links( 'category' ) );
		}
		// Category archive page
		if ( $this->is_set_option( 'tag' ) ) {
			$paths = array_merge( $paths, $this->get_archive_links( 'post_tag' ) );
		}
		// post_type archive page
		if ( $this->is_set_option( 'ptype' ) ) {
			foreach ( array_keys( $this->options['ptype'] ) as $post_type ) {
				$paths = array_merge( $paths, $this->get_archive_links( 'post_type', $post_type ) );
			}
		}
		// taxonomy archive page
		if ( $this->is_set_option( 'tax' ) ) {
			foreach ( array_keys( $this->options['tax'] ) as $taxonomy ) {
				$paths = array_merge( $paths, $this->get_archive_links( 'taxonomy', $taxonomy ) );
			}
		}
		// pages
		if ( $this->is_set_option( 'pages' ) ) {
			$query = new WP_Query(
				array(
					'posts_per_page' => -1,
					'post_type' => 'page',
					'post_status' => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				while( $query->have_posts() ) {
					$query->the_post();
					$paths[] = $this->remove_home_from_url( get_permalink() );
				}
			}
			wp_reset_postdata();
		}

		// posts
		if ( $this->is_set_option( 'posts' ) ) {
			$post_types = array( 'post' );
			foreach ($this->post_types as $post_type) {
				$post_types[] = $post_type->name;
			}
			$post_types[] = 'post';

			$query = new WP_Query(
				array(
					'posts_per_page' => -1,
					'post_type' => $post_types,
					'post_status' => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				while( $query->have_posts() ) {
					$query->the_post();
					$paths[] = $this->remove_home_from_url( get_permalink() );
				}
			}
			wp_reset_postdata();
		}

		sort( $paths );
		$paths = array_unique( $paths );

		return apply_filters( 'wp2html_create_paths_from_database', $paths );
	}
}
