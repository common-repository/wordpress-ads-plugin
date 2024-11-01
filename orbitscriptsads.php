<?php
/*
Plugin Name: Wordpress Ads Plugin
Plugin URI: http://orbitopenadserver.com/how-it-works/for-wordpress.html
Description: You CMS system will communicate with <%SITE_NAME%> thru API, so always will have updated information about channels, their positions and color scheme.
Author: OrbitScripts LLC
Version: 2.0.0
License: GNU/GPLv3
Author URI: http://orbitscripts.com/
Min WP Version:  2.6
*/

define(ORBITSCRIPTSADS_PLUGIN_HOME, WP_PLUGIN_DIR.'/orbitscriptsads/');
//get plugin config
$orbitscriptsads_config = get_option('orbitscriptsads_config', array('step'=>'STEP1'));

//Initialize plugin
function orbitscripts_init() {
    global $orbitscriptsads_config;
    //register scripts && styles
    wp_enqueue_script('orbitads', plugins_url('/js/orbitads.js', __FILE__), array('jquery'), '1.0' );
    wp_enqueue_script('jquery.backgroundPosition', plugins_url('/js/jquery.backgroundPosition.js', __FILE__), array('jquery'), '1.0' );
    wp_enqueue_script('password-strength-meter', "/wp-admin/js/password-strength-meter.js", array('jquery'), '20100331');
    wp_enqueue_script('user-profile', "/wp-admin/js/user-profile.js", array('jquery'), '20100331');
    wp_enqueue_script('wp-ajax-response', "/wp-admin/js/wp-ajax-response.js", array('jquery'), '20100331');
    wp_enqueue_style('orbitads', plugins_url('/css/orbitscriptsads.css', __FILE__), array(), '1.0' );

    //show message for admin
    if (!isset($orbitscriptsads_config['status']) && (!isset($_GET['page']) || ($_GET['page'] != 'manage_ads' && $_GET['page'] != 'orbit_open_ad_server_plugin_create'))) {
        add_action('admin_notices', 'orbitscripts_start_admin_warnings');
    }
    if (isset($orbitscriptsads_config['status']) && $orbitscriptsads_config['status'] == false && (!isset($_GET['page']) || $_GET['page'] != 'manage_ads')) {
        add_action('admin_notices', 'orbitscripts_fail_admin_warnings');
    }
    //include API classes
    include_once ORBITSCRIPTSADS_PLUGIN_HOME.'api/OrbitHostingApi.php';
    include_once ORBITSCRIPTSADS_PLUGIN_HOME.'api/OrbitOpenAdServerApi.php';

}
add_action('init', 'orbitscripts_init');

//Register menu
function orbitscripts_menu() {
    global $orbitscriptsads_config;
    if ( function_exists('add_menu_page') ) {
        add_menu_page( __('Ads Plugin'), __('Ads Plugin'), 10, 'manage_ads', 'orbitscripts_conf', '../wp-content/plugins/orbitscriptsads/images/menu.png');
    }
    if ( function_exists('add_submenu_page') && isset($orbitscriptsads_config['status']) && $orbitscriptsads_config['status'] == true) {
        add_submenu_page('manage_ads', __('Create advertising channel'), __('Create advertising channel'), 10, 'orbit_open_ad_server_plugin_create', 'orbitscripts_create');
    }
}
add_action('admin_menu', 'orbitscripts_menu');


function orbitscripts_create() {
   include_once ORBITSCRIPTSADS_PLUGIN_HOME.'classes/OrbitChannels.php';
   $ch = new OrbitChannels();

   $ch->showPage();
}

//Render config page
function orbitscripts_conf() {
    global $orbitscriptsads_config;
    include ORBITSCRIPTSADS_PLUGIN_HOME.'classes/OrbitAdsConfigPage.php';
    
    //get step
    if (isset($_GET['conf_step'])) {
        $orbitscriptsads_config['step'] =  $_GET['conf_step'];
        update_option('orbitscriptsads_config', $orbitscriptsads_config);
    }

    //get content
    $render = new OrbitAdsConfigPage();
    switch ($orbitscriptsads_config['step']) {
        case 'STEP1':
            $render->step1();
            break;
        case 'STEP2_1':
            $render->step2_1();
            break;
        case 'STEP2_2':
            $render->step2_2();
            break;
        case 'STEP2_2_error':
            $render->step2_2_error();
            break;
        case 'STEP3_1':
            $render->step3_1();
            break;
        case 'STEP3_2':
            $render->step3_2();
            break;
        case 'STEP3_3':
            $render->step3_3();
            break;
        default:
            $render->status();
            break;
    }

    //show content
    $render->show();
}

//admin notice
function orbitscripts_start_admin_warnings() {
    $warn  =  '<div id="orbitscripts-warning" class="updated fade"><p><strong>'.__('WordPress Ads Plug-in is activated.')."</strong> ";
    $warn .= sprintf(__('To start displaying ads you need to complete the <a href="%1$s">final steps</a>.'), "plugins.php?page=manage_ads");
    $warn .= '</p></div>';
    echo $warn;
}

//admin notice
function orbitscripts_fail_admin_warnings() {
    $warn  =  '<div id="orbitscripts-warning" class="updated fade"><p><strong>'.__('Orbit Open Ad Server is not available now.')."</strong> ";
    $warn .= sprintf(__('Please check plug-in <a href="%1$s">settings</a>.'), "plugins.php?page=manage_ads");
    $warn .= '</p></div>';
    echo $warn;
}

//register widgets
include ORBITSCRIPTSADS_PLUGIN_HOME.'classes/OrbitAdsWidget.php';
add_action('widgets_init', create_function('', 'return register_widget("OrbitAdsWidget");'));

//register && add dashboard widget
function orbitscripts_dashboard_widget() {
        global $orbitscriptsads_config;
        $orbitApi = new OrbitOpenAdServerApi($orbitscriptsads_config);
        $stats = $orbitApi->getStat('all');
        $sidebars = wp_get_sidebars_widgets();
        foreach ($sidebars as $sidebar => $widgets) {
            foreach ($widgets as $widget) {
                if (substr($widget, 0, 14) == 'orbitadswidget') {
                    $orbitwidgets[$sidebar][] = $widget;
                }
            }
        }
        $widget_conf = get_option('widget_orbitadswidget');
        foreach ($orbitwidgets as $sidebar => $widgets) {
            foreach ($widgets as $widget) {
                $id_widget = str_replace('orbitadswidget-', '', $widget);
                echo '<div class="table table_content">';
                echo '<p class="sub">'.((!empty($widget_conf[$id_widget]['title'])) ? $widget_conf[$id_widget]['title'].' ('.str_replace('_', ' ',$sidebar).')' : 'Untitled'.' ('.str_replace('-', ' ',$sidebar).')').'</p>
                    <table>
                    <tbody>
                            <tr class="first">
                                    <td class="first b b-posts">'.(isset($stats[$widget_conf[$id_widget]['channel_id']]['clicks']) ? $stats[$widget_conf[$id_widget]['channel_id']]['clicks'] : '0').'</td>
                                    <td class="t posts">Clicks</td>
                            </tr>
                            <tr>
                                    <td class="first b b_pages">'.(isset($stats[$widget_conf[$id_widget]['channel_id']]['impressions']) ? $stats[$widget_conf[$id_widget]['channel_id']]['impressions'] : '0').'</td>
                                    <td class="t pages">Impressions</td>
                            </tr>
                            <tr>
                                    <td class="first b b-cats">'.(isset($stats[$widget_conf[$id_widget]['channel_id']]['alternative_impressions']) ? $stats[$widget_conf[$id_widget]['channel_id']]['alternative_impressions'] : '0').'</td>
                                    <td class="t cats">Alternative Impressions</td>
                            </tr>
                            <tr>
                                    <td class="first b b-tags">'.(isset($stats[$widget_conf[$id_widget]['channel_id']]['earned_admin']) ? $stats[$widget_conf[$id_widget]['channel_id']]['earned_admin'] : '0').'</td>
                                    <td class="t tags">Earnings</td>
                            </tr>
                    </tbody></table>
                </div>';
            }
        }
        echo '<div style="clear: both;"></div>';
}

function orbitscripts_add_dashboard_widgets() {
    global $orbitscriptsads_config;
    if (isset($orbitscriptsads_config['status']) && $orbitscriptsads_config['status'] == true) {
        wp_add_dashboard_widget('orbitscripts_dashboard_widget', 'Wordpress Ad Plug-In Statistics', 'orbitscripts_dashboard_widget');
    }
}
add_action('wp_dashboard_setup', 'orbitscripts_add_dashboard_widgets');

function orbitscripts_install() {
    global $orbitscriptsads_config;
    
    //include installer class
    include ORBITSCRIPTSADS_PLUGIN_HOME.'classes/OrbitInstaller.php';

    //set new API key
    if (empty($orbitscriptsads_config['key'])) {
        $orbitscriptsads_config['key'] = MD5('OrBit*Pen@dsErVeR'.date('Y-m-d H:i:s').'nEwAcTivatIonKeY'.time());
        update_option('orbitscriptsads_config', $orbitscriptsads_config);
    }
    
    //add db config && get installer
    $orbitscriptsads_config['dbname'] = DB_NAME;
    $orbitscriptsads_config['dbuser'] = DB_USER;
    $orbitscriptsads_config['dbpassword'] = DB_PASSWORD;
    $orbitscriptsads_config['dbhost'] = DB_HOST;
    $orbitscriptsads_config['dbcharset'] = DB_CHARSET;
    $inst = new OrbitInstaller($orbitscriptsads_config);

    //get && validate && run installation step
    $step = isset($_POST['step']) ? $_POST['step'] : '';
    $progress = isset($_POST['progress']) ? $_POST['progress'] : '';
    if (!empty($step) && 0 < $step) {
        $step = "step{$step}";
        $inst->{$step}();
    } else if (!empty($progress) && 0 < $progress) {
        $progress = "progress{$progress}";
        $inst->{$progress}();
    } else {
    	$inst->invalidStep();
    }
    //Die Die My Darling (c) Mettalica
    die();
}
add_action('wp_ajax_orbitscripts_install', 'orbitscripts_install');