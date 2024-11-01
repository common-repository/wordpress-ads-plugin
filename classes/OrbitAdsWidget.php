<?php

/**
 * Orbit Open Ad Server Widget Class
 * 
 * @package     Orbit Open Ad Server
 * @subpackage  extension
 * @category    Wordpress
 * @author      OrbitScripts LLC
 */
class OrbitAdsWidget extends WP_Widget {

    protected $config = array();

    function OrbitAdsWidget() {
        parent::WP_Widget(false, 'Orbit Open Ad Server Ads');
    }

    function widget($args, $instance) {
       if (!empty($instance)) {
           if (!($content = wp_cache_get($args['widget_id'], 'orbit_ads_channels'))) {
               extract($args);
               $this->config = get_option('orbitscriptsads_config');
               $content = "{$before_widget}{$before_title}{$instance['title']}{$after_title}
               <script type=\"text/javascript\">
                   var sppc_site      = '{$instance['site_id']}';
                   var sppc_channel   = '{$instance['channel_id']}';
                   var sppc_dimension = '{$instance['dimension']}';
                   var sppc_width     = '{$instance['width']}';
                   var sppc_height    = '{$instance['height']}';
                   var sppc_palette   = '"; 
                   $content .= isset($this->config['palette']) ? $this->config['palette'].';' : '0\';';
                   $content .= "var sppc_user = '{$instance['id_user']}';
               </script>
               <script type=\"text/javascript\" src=\"{$this->config['url']}/show.js\"></script>{$after_widget}";
               wp_cache_set($args['widget_id'], $content, 'orbit_ads_channels', 18000);
           }

           echo $content;
       }
       else {
          echo "<strong>Wordpress Ads Plugin</strong><br />Please specify widget settings in your administrative panel.";
       }
    }

    function update($new_instance, $old_instance) {
        $this->config = get_option('orbitscriptsads_config');
        $id = $new_instance['channel_id'];
        $orbitApi = new OrbitOpenAdServerApi($this->config);
        $channels = $orbitApi->getChannels();
        
        foreach ($channels as $sid => $site) {
            if (isset($site['channels'][$id])) {
                $new_instance['site_id'] = $sid;
                $new_instance['dimension'] = $site['channels'][$id]['dimension'];
                $new_instance['width'] = $site['channels'][$id]['width'];
                $new_instance['height'] = $site['channels'][$id]['height'];
                $new_instance['id_user'] = $site['channels'][$id]['id_user'];
            }
        }

        return $new_instance;
    }

    function form($instance) {
        $this->config = get_option('orbitscriptsads_config');
        if (isset($this->config['status']) && $this->config['status'] == true) {
            $orbitApi = new OrbitOpenAdServerApi($this->config);
            if ($orbitApi->testConnection()) {
                $channels = $orbitApi->getChannels();
                $title = esc_attr($instance['title']);
                $channel_id = $instance['channel_id'];
                if (is_array($channels) && 0 < count($channels)) {
                    $opt = '';
                    foreach ($channels as $site) {
                        foreach ($site['channels'] as $id => $channel) {
                                $opt .= '<option value="'.$id.'"';
                                                    $opt .= ($channel_id == $id) ? 'style="color: #666"' : '';
                                                    $opt .= ($channel_id == $id) ? 'selected="selected"' : '';
                                                    $opt .= '>'.$channel['name'].' ('.$channel['width'].'x'.$channel['height'].')</option>';
                        }
                    }
                    $content .= '<div id="'.$this->get_field_id('select').'">';
                    $content .= '<p><label for="'.$this->get_field_id('title').'">'. __('Title:').'<input id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" class="widefat" type="text" value="'.$title.'" /></label></p>';
                    $content .= '<p><label for="'.$this->get_field_id('channel_id').'">'. __('Select channel:') .'<select id="'. $this->get_field_id('channel_id') .'" name="' . $this->get_field_name('channel_id') .'" class="widefat" type="text"> '.$opt.' </select></label></p>';
                    $content .= '<p style="text-align:right;"><a href="admin.php?page=orbit_open_ad_server_plugin_create"> '.__('Add some').'</a>.</p></div>';
                } else {
                    $content .= __('No available channels.').'<a href="admin.php?page=orbit_open_ad_server_plugin_create"> '.__('Add some').'</a>.';
                }
            } else {
                $this->config['status'] = false;
            }
        } else {
           $content .= __('Ad placing isn\'t available now..<br />').' '.sprintf(__('Please, <a href="%1$s">check plugin settings</a>.'), 'admin.php?page=manage_ads');
        }
        echo $content;
        update_option('orbitscriptsads_config', $this->config);
    }
}
