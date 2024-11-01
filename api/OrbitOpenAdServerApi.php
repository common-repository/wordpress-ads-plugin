<?php

/**
 * Orbit Open Ad Server API client
 *
 * @package     Orbit Open Ad Server
 * @subpackage  library
 * @category    API
 * @author      OrbitScripts LLC
 */

class OrbitOpenAdServerApi {

    //URL for API
    protected $url = null;

    //API key
    protected $key = null;

    //Last response
    private $xml = null;

    //Last error message
    private $errors = array();

    /**
     * Contructor
     * @param array $params
     * @return void
     */
    public function __construct($params = array()) {
        if (count($params) > 0) {
            $this->init($params);
        }
    }

    /**
     * Setup params
     * @param array $params
     * @return void
     */
    public function init($params) {
        $this->url = isset($params['url']) ? $params['url'].'/index.php/xmlapi' : null;
        $this->key = isset($params['key']) ? $params['key'] : null;
        $this->site = isset($params['site']) ? $params['site'] : null;
    }

    /**
     * Test API connection
     * return true if succeses, else return false
     * 
     * @return bool
     */
    public function testConnection() {
        $url = $this->url . '?apiKey=' .  $this->key;
        $url .= "&action=test_connection";
        if ($this->request($url)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Do request
     *
     * @param string $url
     * @return bool
     */
    public function request($url){
        $this->xml = @simplexml_load_file($url);
        if ($this->xml instanceof SimpleXMLElement) {
            try {
                if (intval($this->xml->response->errors->error[0]->code) == 0) {
                    return true;
                } else {
                    $this->errors[] = strval($this->xml->response->errors->error[0]->description);
                    return false;
                }
            } catch (Exception $e) {
                $this->errors[] =  $e->__toString();
                return false;
            }
        } else {
            $this->errors[] = 'Empty response';
            return false;
        }
    }
    
    /**
     * Return all error messages
     *
     * @return array()
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Return last error message
     *
     * @return string
     */
    public function getLastError() {
        return array_pop($this->errors);
    }

    /**
     * Return array with information about
     * color pallettes
     *
     * @return array()
     */
    public function getPalletes() {
        $url = $this->url . '?apiKey=' .  $this->key;
        $url .= '&action=get_palettes';
        if ($this->request($url)) {
            foreach ($this->xml->response->data->palettes->palette as $palette) {
                $result[strval($palette->id)] = array ('name' => strval($palette->name));
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Return array with information about channels for
     * specific site, or for all sites
     *
     * @return array()
     */
    public function getChannels() {
        $url = $this->url . '?apiKey=' .  $this->key;
        $url .= '&action=get_sites_channels';
        $url .= '&siteId=' . $this->site;
        if ($this->request($url)) {
            foreach ($this->xml->response->data->sites->site as $site) {
                $result[strval($site->id)] = array ('name' => strval($site->name),'channels' => '');
                foreach ($site->channels->channel as $channel) {
                        $result[strval($site->id)]['channels'][strval($channel->id)]=array (
                                'name' => strval($channel->name),
                                'dimension' => strval($channel->dimension),
                                'width' => strval($channel->width),
                                'height' => strval($channel->height),
                                'id_user' => strval($channel->userid)
                        );
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Return information about banner's dimensions
     *
     * @return array()
     */
    public function getDimensions() {
        
    }

    /**
     * Get channel stats
     *
     * @param string $period values: today, yesterday, lastweek, lastbusinessweek, thismonth, lastmonth, all
     * @return array $stat Stats for all channels
     */
    public function getStat($period = 'today') {
        $url = $this->url . '?apiKey=' .  $this->key;
        $url .= '&action=get_site_stat';
        $url .= '&siteId='.$this->site;
        $url .= '&period='.$period;
        if ($this->request($url)) {
            foreach ($this->xml->response->data->sites->site as $site) {
                foreach ($site->channels->channel as $channel) {
                    $stat[strval($channel->id)]=array (
                            'clicks' => strval($channel->clicks),
                            'impressions' => strval($channel->impressions),
                            'alternative_impressions' => strval($channel->alternative_impressions),
                            'earned_admin' => strval($channel->earned_admin)
                    );
                }
            }
            return $stat;
        } else {
            return false;
        }
    }

    /**
     * Create new site
     *
     * @return int $id_site
     */
    public function createSite($domain, $site_name, $site_description) {
        $url = $this->url . '?apiKey=' .  $this->key;
        $url .= '&action=create_site';
        $url .= '&surl='.rawurlencode($domain);
        $url .= '&sname='.rawurlencode($site_name);
        $url .= '&sdesc='.rawurlencode($site_description);
        if ($this->request($url)) {
            return intval($this->xml->response->data->sites->site[0]->id);
        } else {
            return false;
        }
    }
    
    /**
     * Create new channel
     *
     * @return int $id_channel
     */
    public function createChannel($params) {
        $url = $this->url . '?apiKey=' .  $this->key;
        $url .= '&action=create_channel';
        $url .= '&siteId=' . $this->site;
        $url .= '&c_name='.rawurlencode($params['name']);
        $url .= '&c_desc='.rawurlencode($params['description']);
        $url .= '&c_fmt='.rawurlencode($params['format']);
        $url .= '&c_adtype='.rawurlencode($params['ad_type']);
        $url .= '&c_adsrc='.rawurlencode($params['ad_sources']);
        
        if (isset($params['cpm_price'])) $url .= '&c_cpm='.rawurlencode($params['cpm_price']);
        if (isset($params['flaterate_price'])) $url .= '&c_flaterate='.rawurlencode($params['flaterate_price']);
        if ($this->request($url)) {
            return intval($this->xml->response->data->channels->channel[0]->id);
        } else {
            return false;
        }
    }

}//end OrbitApi class

/* End of file orbitapi.php */
/* Location: ./includes/orbitapi.php */