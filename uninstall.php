<?php
/**
 * Uninstall BruteFort Plugin
 *
 * This file is executed when the plugin is uninstalled.
 * It removes all plugin data, options, and database tables.
 *
 * @package BruteFort
 * @since 1.0.0
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Check if user has permission to uninstall
if ( ! current_user_can( 'activate_plugins' ) ) {
    return;
}

// Include the main plugin file to access constants and classes
$plugin_file = plugin_dir_path( __FILE__ ) . 'brutefort.php';
if ( file_exists( $plugin_file ) ) {
    require_once $plugin_file;
}

// Only proceed if the plugin class exists
if ( ! class_exists( 'BruteFort' ) ) {
    return;
}

/**
 * Clean up plugin data
 */
function brutefort_cleanup_data() {
    global $wpdb;

    // Plugin options to remove
    $options_to_remove = [
        'brutefort_license_key',
        'brutefort_license_status',
        'brutefort_license_type',
        'brutefort_license_data',
        'brutefort_version',
        'brutefort_db_version',
        'brutefort_settings',
        'brutefort_ip_whitelist',
        'brutefort_ip_blacklist',
        'brutefort_rate_limit_settings',
        'brutefort_login_attempts',
        'brutefort_blocked_ips',
        'brutefort_notification_settings',
        'brutefort_advanced_settings',
        'brutefort_log_settings',
        'brutefort_cleanup_settings'
    ];

    // Remove all plugin options
    foreach ( $options_to_remove as $option ) {
        delete_option( $option );
        delete_site_option( $option ); // For multisite
    }

    // Remove transients
    $transients_to_remove = [
        'brutefort_free_activated',
        'brutefort_pro_activated',
        'brutefort_license_check',
        'brutefort_update_check'
    ];

    foreach ( $transients_to_remove as $transient ) {
        delete_transient( $transient );
        delete_site_transient( $transient ); // For multisite
    }

    // Remove user meta
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
            'brutefort_%'
        )
    );

    // Remove post meta
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
            'brutefort_%'
        )
    );

    // Remove comment meta
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->commentmeta} WHERE meta_key LIKE %s",
            'brutefort_%'
        )
    );

    // Remove custom database tables
    $tables_to_remove = [
        $wpdb->prefix . 'brutefort_logs',
        $wpdb->prefix . 'brutefort_log_details',
        $wpdb->prefix . 'brutefort_ip_settings',
        $wpdb->prefix . 'brutefort_rate_limits',
        $wpdb->prefix . 'brutefort_blocked_ips',
        $wpdb->prefix . 'brutefort_whitelisted_ips'
    ];

    foreach ( $tables_to_remove as $table ) {
        $wpdb->query( $wpdb->prepare( "DROP TABLE IF EXISTS %s", $table ) );
    }

    // Remove scheduled events
    wp_clear_scheduled_hook( 'brutefort_cleanup_logs' );
    wp_clear_scheduled_hook( 'brutefort_license_check' );
    wp_clear_scheduled_hook( 'brutefort_update_check' );

    // Remove capabilities from roles
    $roles = wp_roles();
    $capabilities_to_remove = [
        'manage_brutefort',
        'view_brutefort_logs',
        'manage_brutefort_settings',
        'export_brutefort_data',
        'import_brutefort_data'
    ];

    foreach ( $roles->role_names as $role_name => $role_label ) {
        $role = $roles->get_role( $role_name );
        if ( $role ) {
            foreach ( $capabilities_to_remove as $cap ) {
                $role->remove_cap( $cap );
            }
        }
    }

    // Remove custom post types
    $post_types = ['brutefort_log', 'brutefort_ip_rule'];
    foreach ( $post_types as $post_type ) {
        $posts = get_posts( [
            'post_type' => $post_type,
            'numberposts' => -1,
            'post_status' => 'any'
        ] );

        foreach ( $posts as $post ) {
            wp_delete_post( $post->ID, true );
        }
    }

    // Remove custom taxonomies
    $taxonomies = ['brutefort_ip_category', 'brutefort_log_category'];
    foreach ( $taxonomies as $taxonomy ) {
        $terms = get_terms( [
            'taxonomy' => $taxonomy,
            'hide_empty' => false
        ] );

        foreach ( $terms as $term ) {
            wp_delete_term( $term->term_id, $taxonomy );
        }
    }

    // Clean up uploads directory
    $upload_dir = wp_upload_dir();
    $brutefort_upload_dir = $upload_dir['basedir'] . '/brutefort_uploads/';
    if ( is_dir( $brutefort_upload_dir ) ) {
        brutefort_remove_directory( $brutefort_upload_dir );
    }

    // Clean up logs directory
    $brutefort_log_dir = $upload_dir['basedir'] . '/ur-logs/';
    if ( is_dir( $brutefort_log_dir ) ) {
        brutefort_remove_directory( $brutefort_log_dir );
    }

    // Remove any remaining files in plugin directory (except this file)
    $plugin_dir = plugin_dir_path( __FILE__ );
    $files_to_remove = [
        $plugin_dir . 'assets/build/',
        $plugin_dir . 'dist/',
        $plugin_dir . 'node_modules/',
        $plugin_dir . 'vendor/',
        $plugin_dir . 'src/',
        $plugin_dir . 'scripts/',
        $plugin_dir . 'tests/',
        $plugin_dir . 'webpack.config.js',
        $plugin_dir . 'package.json',
        $plugin_dir . 'package-lock.json',
        $plugin_dir . 'composer.json',
        $plugin_dir . 'composer.lock',
        $plugin_dir . 'tsconfig.json',
        $plugin_dir . 'tailwind.config.js',
        $plugin_dir . 'postcss.config.js',
        $plugin_dir . '.babelrc',
        $plugin_dir . '.eslintrc.js',
        $plugin_dir . '.prettierrc',
        $plugin_dir . '.gitignore',
        $plugin_dir . 'README.md',
        $plugin_dir . 'CHANGELOG.md'
    ];

    foreach ( $files_to_remove as $file ) {
        if ( is_dir( $file ) ) {
            brutefort_remove_directory( $file );
        } elseif ( file_exists( $file ) ) {
            wp_delete_file( $file );
        }
    }
}

/**
 * Recursively remove a directory and its contents
 *
 * @param string $dir Directory path to remove
 */
function brutefort_remove_directory( $dir ) {
    if ( ! is_dir( $dir ) ) {
        return;
    }

    $files = array_diff( scandir( $dir ), ['.', '..'] );
    foreach ( $files as $file ) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if ( is_dir( $path ) ) {
            brutefort_remove_directory( $path );
        } else {
            wp_delete_file( $path );
        }
    }

    // Use WP_Filesystem instead of rmdir
    global $wp_filesystem;
    if ( ! $wp_filesystem ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }
    $wp_filesystem->rmdir( $dir );
}

/**
 * Clean up multisite data if applicable
 */
function brutefort_cleanup_multisite() {
    if ( is_multisite() ) {
        $sites = get_sites();
        foreach ( $sites as $site ) {
            switch_to_blog( $site->blog_id );
            brutefort_cleanup_data();
            restore_current_blog();
        }
    }
}

// Execute cleanup
brutefort_cleanup_data();

// Clean up multisite if applicable
brutefort_cleanup_multisite();

// Clear any remaining caches
if ( function_exists( 'wp_cache_flush' ) ) {
    wp_cache_flush();
}

if ( function_exists( 'w3tc_flush_all' ) ) {
    w3tc_flush_all();
}

if ( function_exists( 'wp_rocket_clean_domain' ) ) {
    wp_rocket_clean_domain();
}

// Final cleanup - remove this file
// Note: This will only work if the file is writable
global $wp_filesystem;
if ( ! $wp_filesystem ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    WP_Filesystem();
}

if ( $wp_filesystem->is_writable( __FILE__ ) ) {
    $wp_filesystem->delete( __FILE__ );
}
