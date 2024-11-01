<?php
/**
 * API client for Orbit Open Ad Server hosting
 *
 * @package     Orbit Open Ad Server
 * @subpackage  library
 * @category    API
 * @author      Vladimir Yants
 */

class OrbitHostingApi {
    //URL for API
    protected $url = 'http://panel.orbitopenadserver.com/';

    //Last response
    private $xml = null;

    /**
     * Contructor
     *
     * @return void
     */
    public function __construct() {
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
                if (intval($this->xml->info->code[0]) != 0) {
                    $this->msg = strval($this->xml->info->msg[0]);
                    return false;
                } else {
                    return true;
                }
            } catch (Exception $e) {
                $this->msg = $e->__toString();
                return false;
            }
        } else {
            $this->msg = 'Non XML response';
            return false;
        }
    }

    /**
     * Check domain name
     *
     * @param string $domain
     * @return bool
     */
    public function checkDomain($domain) {
        $url = $this->url . 'check.php?fmt=xml';
        $url .= "&domain=".rawurlencode($domain);
        if ($this->request($url)) {
            $this->msg = strval($this->xml->info->msg[0]);
            return true;
        } else {
            return false;
        }
    }
    /**
     * Check email
     *
     * @param string $email
     * @return bool
     */
    public function checkEmail($email) {
        $url = $this->url . 'check.php?fmt=xml';
        $url .= "&email=".rawurlencode($email);
        if ($this->request($url)) {
            $this->msg = strval($this->xml->info->msg[0]);
            return true;
        } else {
            return false;
        }
    }
    /**
     * Check password
     *
     * @param string $pwd
     * @return bool
     */
    public function checkPassword($pwd) {
        $url = $this->url . 'check.php?fmt=xml';
        $url .= "&password=".rawurlencode($pwd);
        if ($this->request($url)) {
            $this->msg = strval($this->xml->info->msg[0]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Request account
     *
     * @param string $domain
     * @param string $email
     * @param string $pwd
     * @return bool
     */
    public function requestAccount($domain, $email, $pwd) {
        $url = $this->url . 'request.php?fmt=xml';
        $url .= "&domain=".rawurlencode($domain);
        $url .= "&mail=".rawurlencode($email);
        $url .= "&password=".rawurlencode($pwd);
        if ($this->request($url)) {
            $this->msg = strval($this->xml->info->msg[0]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Activate account
     *
     * @param string $domain
     * @param string $code
     * @return bool
     */
    public function activateAccount($domain, $code) {
        $url = $this->url . 'verification.php?fmt=xml';
        $url .= "&domain=".rawurlencode($domain);
        $url .= "&key=".rawurlencode($code);
        if ($this->request($url)) {
            $this->msg = strval($this->xml->info->msg[0]);
            return true;
        } else {
            return false;
        }
    }

    public function getMsg() {
        return $this->msg;
    }
}
