<?php

/**
 * Plugin Name: Woo AI
 * Description: Using artificial intelligence, it writes descriptions of woocommerce products automatically
 * Text Domain: momoacgwc
 * Domain Path: /languages
 * Author: MoMo Themes
 * Version: 1.2.4
 * Author URI: http://www.momothemes.com/
 * Requires at least: 5.4
 * Tested up to: 6.6.2
 */
/**
 * Freemius SDK Integration
 */
if ( !function_exists( 'momoacgwc_fs' ) ) {
    /**
     * Create a helper function for easy SDK access.
     *
     * @return $momoacgwc_fs
     */
    function momoacgwc_fs() {
        global $momoacgwc_fs;
        if ( !isset( $momoacgwc_fs ) ) {
            // Include Freemius SDK.
            require_once __DIR__ . '/freemius/start.php';
            $momoacgwc_fs = fs_dynamic_init( array(
                'id'             => '15141',
                'slug'           => 'auto-product-description-writer',
                'type'           => 'plugin',
                'public_key'     => 'pk_97c6495005e61f8b0054cb8983a11',
                'is_premium'     => false,
                'premium_suffix' => 'Premium',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                    'slug'       => 'momoacgwc',
                    'first-path' => 'admin.php?page=momoacgwc-getting-started',
                ),
                'is_live'        => true,
            ) );
        }
        return $momoacgwc_fs;
    }

    // Init Freemius.
    momoacgwc_fs();
    // Signal that SDK was initiated.
    do_action( 'momoacgwc_fs_loaded' );
}
/**
 * Plugin main class
 */
class MoMo_ACG_For_Woocommerce {
    /**
     * Plugin Version
     *
     * @var string
     */
    public $version = '1.2.4';

    /**
     * Plugin URL
     *
     * @var string
     */
    public $plugin_url;

    /**
     * Plugin Path
     *
     * @var string
     */
    public $plugin_path;

    /**
     * Plugin Name
     *
     * @var string
     */
    public $name;

    /**
     * Plugin Functions Instance
     *
     * @var MoMo_Basic_Functions_ACG_WC
     */
    public $fn;

    /**
     * Plugin Language Instance
     *
     * @var MoMo_ACG_WC_Lang_All
     */
    public $lang;

    /**
     * Plugin API Instance
     *
     * @var MoMo_ACG_WC_Rest_API
     */
    public $api;

    /**
     * Recomendated Products
     *
     * @var Momo_Acg_Related_Actions;
     */
    public $related;

    /**
     * Chatbot Instance
     *
     * @var MoMo_ACG_Chatbot_Frontned
     */
    public $chatbot;

    /**
     * Embeddings Instance
     *
     * @var MoMo_ACGWC_Embeddings_Model
     */
    public $embeddings;

    /**
     * Table Function
     *
     * @var string
     */
    public $tblfn;

    /**
     * Tables
     *
     * @var string
     */
    public $tables;

    /**
     * Plugin URL
     *
     * @var string
     */
    public $momoacgwc_url;

    /**
     * Plugin Assets
     *
     * @var string
     */
    public $momoacgwc_assets;

    /**
     * RSS Feed Cron
     *
     * @var MoMo_RssFeed_Cron
     */
    public $rssfeedcron;

    /**
     * Rss Feed Function
     *
     * @var MoMo_RssFeed_Functions
     */
    public $rssfeedfn;

    /**
     * Search log table
     *
     * @var MoMo_Acgwc_Search_Table
     */
    public $searchlogtb;

    /**
     * Search log functions
     *
     * @var Momo_ACGWC_Search_Logger
     */
    public $searchlogger;

    /**
     * Search log background job
     *
     * @var Momo_ACGWC_Search_Background
     */
    public $searchlogbg;

    /**
     * Constructor
     */
    public function __construct() {
        add_action(
            'wp_loaded',
            array($this, 'initialize_woo_related'),
            10,
            1
        );
        add_action( 'plugins_loaded', array($this, 'momo_acg_wc_plugin_loaded') );
        /** For tables and stuff  */
        /** Maybe Premium Only */
        include_once 'includes/class-momo-acgwc-table-functions.php';
        $this->tblfn = new MoMo_ACGWC_Table_Functions();
        $this->tables = array('momo_acg_cb_trainings_list');
        $this->tables = apply_filters( 'momo_acgwc_tables', $this->tables );
        register_activation_hook( __FILE__, array($this, 'momo_acgwc_activate') );
        add_action( 'init', array($this, 'momo_check_if_table_exists_or_not') );
        /** For tables and stuff  */
        momoacgwc_fs()->add_action( 'after_uninstall', array($this, 'momoacgwc_fs_uninstall_cleanup') );
    }

    /**
     * Uninstall
     */
    public function momoacgwc_fs_uninstall_cleanup() {
        delete_option( 'momo_acg_wc_edit_product_settings' );
        delete_option( 'momo_acg_wc_openai_settings' );
    }

    /**
     * Plugin Loaded
     */
    public function momo_acg_wc_plugin_loaded() {
        $this->plugin_url = plugin_dir_url( __FILE__ );
        $this->plugin_path = __DIR__ . '/';
        $this->name = esc_html__( 'Auto Product Description Writer', 'momoacgwc' );
        $this->momoacgwc_url = path_join( plugins_url(), basename( __DIR__ ) );
        $this->momoacgwc_assets = str_replace( array('http:', 'https:'), '', $this->momoacgwc_url ) . '/assets/';
        add_action( 'init', array($this, 'momo_acg_wc_plugin_init'), 10 );
    }

    /**
     * Initialize Woo Related Products
     */
    public function initialize_woo_related() {
        // Related Products v1.2.1.
        include_once 'related/class-momo-acg-related-actions.php';
        $this->related = new Momo_Acg_Related_Actions();
    }

    /**
     * Plugin Init
     *
     * @return void
     */
    public function momo_acg_wc_plugin_init() {
        load_plugin_textdomain( 'momoacgwc', false, 'momo-acg-for-woocommerce/languages' );
        include_once 'includes/class-momo-basic-functions-acg-wc.php';
        include_once 'includes/class-momo-acg-wc-lang-all.php';
        include_once 'includes/class-momo-acg-wc-rest-api.php';
        $this->fn = new MoMo_Basic_Functions_ACG_WC();
        $this->lang = new MoMo_ACG_WC_Lang_All();
        $this->api = new MoMo_ACG_WC_Rest_API();
        if ( is_admin() ) {
            include_once 'includes/admin/class-momo-acg-wc-admin-init.php';
            include_once 'includes/admin/class-momo-acg-wc-admin-ajax.php';
            include_once 'includes/admin/metabox/class-momo-acg-wc-metabox.php';
            do_action( 'momo_acg_wc_admin_init' );
        }
        // Related Products v1.2.1.
        include_once 'related/class-momo-acg-related-products.php';
        // Chatbot v1.2.2.
        $this->momo_acg_wc_chatbot_init();
        // Feeds v1.2.3.
        // RSS Feed / Auto Blog.
        $this->momo_acg_rss_feed_init();
        // Search Log v1.2.4.
        $this->momo_acg_search_log_init();
        if ( is_admin() ) {
            add_action( 'admin_menu', array($this, 'momoacgwc_set_getting_started_menu'), 20 );
        }
    }

    /**
     * RSS Feed to content.
     */
    public function momo_acg_rss_feed_init() {
        // include_once 'cpt-momoacgwc/class-momo-cpt-momoacgwc.php';
        if ( is_admin() ) {
            include_once 'feeds/admin/class-momo-acg-rssfeed-admin.php';
            include_once 'feeds/admin/class-momo-acg-autoblog-admin.php';
            include_once 'feeds/admin/class-momo-acg-rssfeed-admin-ajax.php';
        }
        include_once 'feeds/class-momo-rssfeed-cron.php';
        include_once 'feeds/class-momo-rssfeed-functions.php';
        $this->rssfeedcron = new MoMo_RssFeed_Cron();
        $this->rssfeedfn = new MoMo_RssFeed_Functions();
    }

    /**
     * Init search log
     *
     * @return void
     */
    public function momo_acg_search_log_init() {
        include_once 'search/class-momo-acgwc-search-table.php';
        $this->searchlogtb = new MoMo_Acgwc_Search_Table();
        include_once 'search/class-momo-acgwc-search-logger.php';
        $this->searchlogger = new Momo_ACGWC_Search_Logger();
        include_once 'search/class-momo-acgwc-search-background.php';
        $this->searchlogbg = new Momo_ACGWC_Search_Background();
        if ( is_admin() ) {
            include_once 'search/admin/class-momo-acgwc-search-admin.php';
        }
        include_once 'search/class-momo-acgwc-search-log-cron.php';
    }

    /**
     * Get getting started menu
     *
     * @return void
     */
    public function momoacgwc_set_getting_started_menu() {
        add_submenu_page(
            'momoacgwc',
            esc_html__( 'Getting Started', 'momoacgwc' ),
            esc_html__( 'Getting Started', 'momoacgwc' ),
            'manage_options',
            'momoacgwc-getting-started',
            array($this, 'momoacgwc_render_getting_started_page'),
            11
        );
    }

    /**
     * Render getting started page
     */
    public function momoacgwc_render_getting_started_page() {
        global $momoacgwc;
        require_once $momoacgwc->plugin_path . 'includes/admin/pages/page-momo-acgwc-getting-started.php';
    }

    /**
     * Chatbot Initialization
     *
     * @return void
     */
    public function momo_acg_wc_chatbot_init() {
        if ( is_admin() ) {
            include_once 'chatbot/admin/class-momo-acgwc-chatbot-admin.php';
            include_once 'chatbot/admin/class-momo-wc-chatbot-admin-ajax.php';
        }
        include_once 'chatbot/class-momo-acgwc-chatbot-shortcodes.php';
        include_once 'chatbot/class-momo-acgwc-chatbot-frontend.php';
        include_once 'chatbot/class-momo-acgwc-chatbot-ajax.php';
        $this->chatbot = new MoMo_ACGWC_Chatbot_Frontned();
    }

    /**
     * Activation Functions
     */
    public function momo_acgwc_activate() {
        foreach ( $this->tables as $option_table ) {
            $this->tblfn->momo_create_option_table( $option_table );
        }
        do_action( 'momo_acgwc_activate' );
    }

    /**
     * Deactivation Functions
     *
     * @return void
     */
    public function momo_acgwc_deactivate() {
        foreach ( $this->tables as $option_table ) {
            $this->tblfn->momo_delete_option_table( $option_table );
        }
        do_action( 'momo_acgwc_deactivate' );
    }

    /**
     * Check if table exist or not ( Create if dont exists)
     */
    public function momo_check_if_table_exists_or_not() {
        global $wpdb;
        foreach ( $this->tables as $option_table ) {
            // Define a cache key for the specific table existence check.
            $cache_key = 'momo_table_exists_' . $option_table;
            $cached_result = wp_cache_get( $cache_key, 'momo_custom_options' );
            if ( false === $cached_result ) {
                // If the cache is empty, perform the table existence check for this table.
                $db_table = $wpdb->get_var( 
                    // phpcs:ignore
                    $wpdb->prepare( 'SHOW TABLES LIKE %s', $option_table )
                 );
                if ( $db_table !== $option_table ) {
                    $this->tblfn->momo_create_option_table( $option_table );
                }
                // Store the result in the cache for future use.
                wp_cache_set( $cache_key, true, 'momo_custom_options' );
            }
        }
    }

}

$GLOBALS['momoacgwc'] = new MoMo_ACG_For_Woocommerce();