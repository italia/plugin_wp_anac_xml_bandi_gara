<?php

add_action( 'admin_menu', 'register_avcp_wpgov_menu_page' );

function register_avcp_wpgov_menu_page(){
    if ( empty ( $GLOBALS['admin_page_hooks']['impostazioni-wpgov'] ) ) {
        add_menu_page('Impostazioni soluzioni WPGOV.IT', 'WP<b>Gov</b>.it', 'manage_options', 'impostazioni-wpgov', 'avcp_wpgov_settings', '
dashicons-shield-alt', 40);
    }
}

function avcp_wpgov_settings () {
    
    $date1 = date('Y-m-d', time());
    $date2 = date('Y-m-d', strtotime(get_option('wpgov_ddate')) );
    if ( floor( (strtotime($date1)-strtotime($date2))/(60*60*24) ) > 6 ) {
        update_option('wpgov_ddate', $date1);
        include(plugin_dir_path(__FILE__) . 'dona.php');
    } else {
        include(plugin_dir_path(__FILE__) . 'dispatcher.php');
    }
}

add_filter('wpgov_s', 'wpgov_s_avcp');
function wpgov_s_avcp( $array ) {
    if ( empty($array) ) {
        $array = array();
    }
    array_push( $array, array('ANAC XML', 'dashicons-portfolio', 'avcp/wpgov/settings.php') );
    return $array;
}
?>
