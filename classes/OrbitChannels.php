<?php
/**
 * Create channel page
 *
 * @package     Orbit Open Ad Server
 * @subpackage  extension
 * @category    Wordpress
 * @author      Vladimir Yants
 */

class OrbitChannels {
    
    function __construct() {
        $this->config = get_option('orbitscriptsads_config');
    }

    function showPage() {
         $ok = true;
         $channel_name = isset($_POST['channel_name']) ? $_POST['channel_name'] : '';
         $channel_description = isset($_POST['channel_description']) ? $_POST['channel_description'] : '';
         $ad_type = isset($_POST['ad_type']) ? $_POST['ad_type'] : '';
         $format = isset($_POST['format']) ? $_POST['format'] : '';
         $ad_sources = isset($_POST['ad_sources']) ? $_POST['ad_sources'] : '';
         $allowed_prices = isset($_POST['allowed_prices']) ? $_POST['allowed_prices'] : '';
         $cpm_price = isset($_POST['cpm_price']) ? $_POST['cpm_price'] : '';
         $flaterate_price = isset($_POST['flaterate_price']) ? $_POST['flaterate_price'] : '';

         switch (true) {
             case empty($channel_name):
                 $ok = false;
             case empty($channel_description):
                 $ok = false;
             case empty($ad_type):
                 $ok = false;
             case empty($format):
                 $ok = false;
             case empty($ad_sources):
                 $ok = false;
         }
         
         if (!$ok) {
             $content = '<div class="wrap">
                            <div class="icon32" id="icon-link-manager"><br></div>
                            <h2>Create new channel</h2>
                            <form class="validate" method="post">
                            <input type="hidden" name="page" value="orbit_open_ad_server_plugin_create"/>';

                // Formats
                $formats = array(
                    "horizontal" => array(
                        12 => '180 x 150 Small Rectangle',
                        3 => '234 x 60 Half Banner',
                        13 => '300 x 250 Medium Rectangle',
                        14 => '336 x 280 Large Rectangle',
                        2 => '468 x 60 Banner',
                        1 => '728 x 90 Leaderboard'),
                    "vertical" => array(
                        6 => '120 x 240 Vertical Banner',
                        4 => '120 x 600 Skyscraper',
                        5 => '160 x 600 Wide Skyscraper'),
                    "square" => array(
                        10 => '125 x 125 Button',
                        9 => '200 x 200 Small Square',
                        8 => '250 x 250 Medium Rectangle')
                );
                $opt_form = '';
                foreach ($formats as $group => $options) {
                    $opt_form .= '<optgroup label="'.$group.'">';
                    foreach ($options as $id => $text) {
                        $opt_form .= '<option value="'.$id.'"';
                        $opt_form .= ($format == $id) ? 'selected="selected"' : '';
                        $opt_form .= '>'.$text.'</option>';
                    }
                    $opt_form .= '</optgroup><optgroup></optgroup>';
                }
              $content .=  '<table class="form-table">';
              //name
              $content .=  '<tr valign="top" class="form-field form-required">
                                        <th scope="row">
                                                <label for="channel_name">Name
                                                <span class="description">(required)</span></label>

                                        </th>
                                        <td>
                                                <input class="regular-text" id="channel_name" name="channel_name" type="text" value="'.$channel_name.'"/>

                                                <br/>
                                                <span class="description">Example: My Channel 1</span>
                                        </td>
                                </tr>';
              //description
               $content .=  '<tr valign="top" class="form-field form-required">
                                        <th scope="row">
                                                <label for="channel_description">Description
                                                <span class="description">(required)</span></label>

                                        </th>
                                        <td>
                                                <input class="regular-text" id="channel_description" value="'.$channel_description.'" name="channel_description"></textarea>
                                        </td>
                                </tr>';
               //ad type
              $content .= '<tr valign="top" class="form-field form-required">
                                        <th scope="row">
                                                <label for="ad_type">Ad Type
                                                <span class="description"></span></label>
                                        </th>
                                        <td>
                                                <select onchange="changeAdTypes()" id="ad_type" name="ad_type" type="text">';
              $content .= '<option value="text_img"';
              $content .= ('text_img' == $ad_type) ? 'selected="selected"' : '';
              $content .= '>Text & Image</option><option value="text"';
              $content .= ('text' == $ad_type) ? 'selected="selected"' : '';
              $content .= '>Text Only</option><option value="img"';
              $content .= ('img' == $ad_type) ? 'selected="selected"' : '';
              $content .=                       '>Image Only</option></select>
                                                <br/>
                                                <span class="description">Allowed ads formats in this channel</span>
                                        </td>
                                </tr>';
              //format
              $content .= '<tr valign="top" class="form-field form-required">
                                        <th scope="row">
                                                <label for="format">Banners size
                                                <span class="description"></span></label>
                                        </th>
                                        <td>
                                                <select onchange="changeAdTypes()" id="format" name="format" type="text">
                                                    '.$opt_form.'
                                                </select>
                                                <br/>
                                                <span class="description">The size of the ad channel</span>
                                        </td>
                                </tr>';
              $content .= '<tr><td></td><td><div style="float: left;" id="slots_preview_container" style=""></div></td></tr>';
              $content .= '<script type="text/javascript">
                                jQuery(changeAdTypes());
                            </script>';
              //ad sources
              $content .= '<tr valign="top" class="form-field form-required">
                                        <th scope="row">
                                                <label for="ad_sources">Ad sources
                                                <span class="description"></span></label>
                                        </th>
                                        <td>
                                                <select id="ad_sources" name="ad_sources" type="text"> <option value="adv_xml"';
              $content .= ('adv_xml' == $ad_sources) ? 'selected="selected"' : '';
              $content .=                          '>Advertisers & XML Feeds</option><option value="adv"';
              $content .= ('adv' == $ad_sources) ? 'selected="selected"' : '';
              $content .=                           '>Advertisers Only</option><option value="xml"';
              $content .= ('xml' == $ad_sources) ? 'selected="selected"' : '';
              $content .=                           '>Feeds Only</option></select>
                                                <br/>
                                                <span class="description">Ad channel sources</span>
                                        </td>
                                </tr>';

              //Prices
              $content .= '<tr valign="top" class="form-field form-required">
                                        <th scope="row">
                                                <label for="allowed_prices">Ad Pricing
                                                <span class="description"></span></label>
                                        </th>
                                        <td>
                                                <select onchange="tooglePrice()" id="allowed_prices" name="allowed_prices" type="text"> <option value="cpm_flatrate"';
              $content .= ('cpm_flatrate' == $allowed_prices) ? 'selected="selected"' : '';
              $content .=                          '>CPM & Flatrate</option><option value="cpm"';
              $content .= ('cpm' == $allowed_prices) ? 'selected="selected"' : '';
              $content .=                           '>CPM</option><option value="flatrate"';
              $content .= ('flatrate' == $allowed_prices) ? 'selected="selected"' : '';
              $content .=                           '>Flatrate</option></select>
                                                <br/>
                                                <span class="description">Ad channel sources</span>
                                        </td>
                                </tr>';
               //CPM
               $content .=  '<tr id="cpm_prices" valign="top" class="form-field form-required">
                                        <th scope="row">
                                                <label for="cpm_price">Price for 1k impressions
                                                <span class="description">(required)</span></label>

                                        </th>
                                        <td>
                                                <input class="regular-text" id="cpm_price" value="'.$cpm_price.'" name="cpm_price" />
                                        </td>
                                </tr>';
               //Flatrate
               $content .=  '<tr id="flaterate_prices" valign="top" class="form-field form-required">
                                        <th scope="row">
                                                <label for="flaterate_price">Price for 3 days placing
                                                <span class="description">(required)</span></label>

                                        </th>
                                        <td>
                                                <input class="regular-text" id="flaterate_price" value="'.$flaterate_price.'" name="flaterate_price"></textarea>
                                        </td>
                                </tr>';
              //end table
              $content .= '</table>
                        <p class="submit">
                            <input class="button-primary" id="opt1_submit" type="submit" value="Create"/>
                        </p>';
         } else {
            $orbitApi = new OrbitOpenAdServerApi($this->config);
            if (!$orbitApi->testConnection()) $this->config['status'] = false;
            if ($ad_sources == 'adv') {
                $ad_sources = 'advertisers';
            } else if ($ad_sources == 'xml') {
                $ad_sources = 'xml_feeds';
            } else {
                $ad_sources = 'advertisers,xml_feeds';
            }
            if ($ad_type == 'text') {
                $ad_type = 'text';
            } else if ($ad_type == 'img') {
                $ad_type = 'image';
            } else {
                $ad_type = 'text,image';
            }
            $params = array (
                'name' => $channel_name,
                'description' => $channel_description,
                'ad_type' => $ad_type,
                'format' => $format,
                'ad_sources' => $ad_sources
            );
            
            if (!empty($cpm_price)) $params['cpm_price'] = $cpm_price;
            if (!empty($flaterate_price)) $params['flaterate_price'] = $flaterate_price;

            if ($res = $orbitApi->createChannel($params)) {
                $this->config['status'] = true;
                 $content .= '<div class="wrap"><div class="icon32" id="icon-link-manager"><br></div>
                            <h2>Create new channel</h2><div class="updated fade" id="orbitscripts-warning">
                     <p><strong>New channel is successfully created.</strong></p></div>';
                 $content .= '<p>
                     You can place new created channel in the blog. Please use the interface – Windgets Management.
                     Find the «WordPress Ads Plug-in Widget" widgets from the left and drag and drop it on the widget area.
                     Select the channel from the list and save the widget.
                     You may <a href="admin.php?page=orbit_open_ad_server_plugin_create">create another channel</a> if it is needed.
                     </p><p style="text-align: right;"><a href="widgets.php" class="button">Widgets Management</a></p></div>';
            }
         }

        echo $content.'</form><script>if(typeof wpOnload==\'function\')wpOnload();</script>';
        update_option('orbitscriptsads_config', $this->config);
    }
}