<?php 

/*Redirect users to profile page on login*/

function gw_login_redirect($redirect_to, $request, $user) {
    if(isset($user) && isset($user->id))
        return bp_core_get_user_domain($user->id);
    return $redirect_to;
}
add_filter('login_redirect','gw_login_redirect',10,3);

/*
UMP Hooks to connect member levels from UMP to BP
*/
function gw_update_level($userid, $levelid) {
    switch($levelid)
    {
        case 6:
        case 4: // wwoofer
        bp_set_member_type($userid, 'wwoofer');

        break;
        
        case 5: //Host
        bp_set_member_type($userid, 'host');
        break;
        
    default:
        bp_set_member_type($userid, '');
    break;
    }
}
add_action("ihc_new_subscription_action","gw_update_level",10,2);
add_action("ihc_action_after_subscription_activated","gw_update_level",10,2);

function gw_remove_level($userid, $levelid) {
    bp_set_member_type($userid, '');
}
add_action("ihc_action_after_subscription_delete","gw_remove_level",10,2);








add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

function theme_enqueue_styles() {
wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    //wp_enqueue_style( 'gw-style', get_stylesheet_directory_uri() . '/style.css' );
    //wp_enqueue_script( 'kleo-js', get_stylesheet_directory_uri() . '/assets/js/kleo.js' );
//$fonts_path = get_stylesheet_directory_uri() . '/assets/css/fontello.min.css';
 //wp_enqueue_style( 'fontello', $fonts_path );
}
	

//======================================================================
// ADD LINK TO QUIROZ.CO
//======================================================================
function my_loginfooter() { ?>
    <p style="text-align: center; margin-top: 1em;">
    <a style="color: #000; text-decoration: none;" href="http://gippslandweb.com.au">GippslandWeb
        </a>
    </p>
<?php }
add_action('login_footer','my_loginfooter');	


//======================================================================
// CUSTOM DASHBOARD
//======================================================================
// ADMIN FOOTER TEXT
function remove_footer_admin () {
    echo "WWOOF Theme by GippslandWeb";
} 

add_filter('admin_footer_text', 'remove_footer_admin');



function kleo_bp_count_member_types( $member_type = '' ) {
    if ( ! bp_is_root_blog() ) {
        switch_to_blog( bp_get_root_blog_id() );
    }
    global $wpdb;
    $sql = array(
        'select' => "SELECT t.slug, tt.count FROM {$wpdb->term_taxonomy} tt LEFT JOIN {$wpdb->terms} t",
        'on'     => 'ON tt.term_id = t.term_id',
        'where'  => $wpdb->prepare( 'WHERE tt.taxonomy = %s', 'bp_member_type' ),
    );
    $members_count = $wpdb->get_results( join( ' ', $sql ) );
    $members_count = wp_filter_object_list( $members_count, array( 'slug' => $member_type ), 'and', 'count' );
    $members_count = array_values( $members_count );
    if( isset( $members_count[0] ) && is_numeric( $members_count[0] ) ) {
        $members_count = $members_count[0];
    }else{
        $members_count = 0;
    }
    restore_current_blog();
    return $members_count;
}

//Add Member types to the Members page
add_action( 'bp_members_directory_member_types', 'kleo_bp_member_types_tabs' );
function kleo_bp_member_types_tabs() {
    if( ! bp_get_current_member_type() ){
        $member_types = bp_get_member_types( array(), 'objects' );
        if( $member_types ) {
            foreach ( $member_types as $member_type ) {
                if ( $member_type->has_directory == 1 ) {
                    echo '<li id="members-' . esc_attr($member_type->name) . '" class="bp-member-type-filter">';
                    echo '<a href="' . bp_get_members_directory_permalink() . 'type/' . $member_type->directory_slug . '/">' . sprintf('%s <span>%d</span>', $member_type->labels['name'], kleo_bp_count_member_types($member_type->name)) . '</a>';
                    echo '</li>';
                }
            }
        }
    }
}


add_filter( 'bp_before_has_members_parse_args', 'kleo_bp_set_has_members_type_arg', 10, 1 );
function kleo_bp_set_has_members_type_arg( $args ) {
    $member_type = bp_get_current_member_type();
    $member_types = bp_get_member_types(array(), 'names');
    if ( isset( $args['scope'] ) && !isset( $args['member_type'] ) && in_array( $args['scope'], $member_types ) ) {
        if( $member_type ) {
            unset( $args['scope'] );
        }else{
            $args['member_type'] = $args['scope'];
        }
    }
    return $args;
}


//Style the login page
function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/wwoofau-logo.jpg);
            padding-bottom: 10px;
width:222px!important;
height:222px!important;
background-size:222px!important;
background-color:#ffffff!important;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );

//=============================================
//    WP CHAT MESSAGING FOR BUDDYPRESS 
//===============================================
// send message button

/*add_action("bp_profile_header_meta", function() {
    global $current_user;
    $user_id = bp_displayed_user_id();
    if ( ! $current_user->ID || ! $user_id || $current_user->ID == $user_id ) return;
    ob_start(); wp_title(); $title = ob_get_clean();
  
    ?>
        <div>
            <?php wpc_contact_user_modal_link( get_userdata( $user_id ), array( 'exit_title' => $title ) ); ?>
        </div>
    <?php
});
*/

// ==================================link wpChat user profile to Buddypress profile

add_filter("wpc_get_user_links", function($links, $user_id) {
    if ( ! empty( $links->profile ) ) {
        $links->profile = bp_core_get_user_domain($user_id);
    }
    return $links;
}, 10, 2);

//================================== ADD MESSAGE COUNT TO MENU ITEM ==============
add_filter('wp_nav_menu_items', function( $items ) {
 
	$target = '<li id="menu-item-7100" class="menu-item menu-item-type-post_type menu-item-object-page"><a href="http://lakes.com.au/wpc-messages/"><span>Messages</span></a></li>';
	$count = class_exists('wpChats') ? do_shortcode('[wpc type="user" part="unread_count"]') : '0';
	$replace = '<li id="menu-item-7100" class="menu-item menu-item-type-post_type menu-item-object-page"><a href="http://lakes.com.au/wpc-messages/"><span>Messages</span> (' . $count . ')</a></li>';

	$items = str_replace( $target, $replace, $items );

	return $items;
 
});


// ======================  LOGIN REDIRECT ======================== 

/**
 * Redirect user after successful login.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 */
function my_login_redirect( $redirect_to, $request, $user ) {
	//is there a user to check?
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		//check for admins
		if ( in_array( 'administrator', $user->roles ) ) {
			// redirect them to the default place
			return $redirect_to;
		} else {
			return '/members/'.bp_core_get_username($user->ID);
		}
	} else {
		return $redirect_to;
	}
}

add_filter( 'login_redirect', 'my_login_redirect', 10, 3 );

/** =====================  LOG OUT REDIRECT  =====================
* add_filter( 'logout_url', 'my_logout_page', 10, 2 );
* function my_logout_page( $logout_url, $redirect ) {
*     return home_url( '/my-logout-page/?redirect_to=' . $redirect );
* }
*/