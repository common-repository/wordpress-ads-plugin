<?php
/**
 * OrbitInstaller - installer for Orbit Open Ad Server
 *
 * @package     Orbit Open Ad Server
 * @subpackage  Library
 * @category    Utilities
 * @author      Vladimir Yants
 */

class OrbitInstaller {
    private $_extensionsTest=array(
            'Mbstring',
            'Pdo',
            'Pdo_Sqlite',
            'Pdo_Mysql',
            'Iconv',
            'Simplexml',
            'Json'
    );
    private $_extensionsMissed=array();
    private $_optionsWrong=array();
    private $_phpVersion='';
    private $_permissions = array(
          'uploads',
          'js',
          'css',
          'files',
          'application/cache',
          'application/logs',
          'application/config/plugins',
          'application/config'
        );
    private $byte_city = "http://bytecity.com/reg";

    /*
     * Constructor
     *
     * @param array() $params
     * @return void
     */
    public function __construct($params = array ()) {
        if (!empty($params)) {
            $this->init($params);
        }
    } //end __construct()

    /*
     * Initialize class vars
     *
     * @param array() $params
     * @return void
     */
    public function init ($params) {
        if (!empty($params['path'])) {
            $this->path = $params['path'];
        } else {
            $this->err('Path isn\'t set');
        }
        if (!empty($params['email'])) {
            $this->email = $params['email'];
        } else {
            $this->err('Email isn\'t set');
        }
        if (!empty($params['password'])) {
            $this->password = $params['password'];
        } else {
            $this->err('Password isn\'t set');
        }
        if (!empty($params['dbname'])) {
            $this->dbname = $params['dbname'];
        } else {
            $this->err('DB user isn\'t set');
        }
        if (!empty($params['dbpassword'])) {
            $this->dbpassword = $params['dbpassword'];
        } else {
            $this->err('DB password isn\'t set');
        }
        if (!empty($params['dbhost'])) {
            $this->dbhost = $params['dbhost'];
        } else {
            $this->err('DB host isn\'t set');
        }
        if (!empty($params['dbuser'])) {
            $this->dbuser = $params['dbuser'];
        } else {
            $this->err('DB user isn\'t set');
        }
        if (!empty($params['dbcharset'])) {
            $this->dbcharset = $params['dbcharset'];
        } else {
            $this->err('DB charset isn\'t set');
        }
        if (!empty($params['key'])) {
            $this->key = $params['key'];
        } else {
            $this->err('key isn\'t set');
        }
   } //end init()

    /*
     * Step 1. Check server configuration
     *
     * @return void
     */
    public function step1() {
        //get loaded php extensions
        $this->_extensions = get_loaded_extensions ();
		$this->_extensions = array_change_key_case(array_flip($this->_extensions));

        //check PHP version
        $version = explode('.', PHP_VERSION);
        $php_major_version = $version[0];
        $php_minor_version = $version[1];
        $php_release_version = $version[2];

        if (5 != $php_major_version || 1 > $php_minor_version) {
            $this->_phpVersion = "PHP version 5.1.x and higher is required.";
        }

        //check extensions
        foreach ($this->_extensionsTest as $ext) {
            if (!array_key_exists(strtolower($ext),$this->_extensions)) {
                $this->_extensionsMissed[] = $ext;
            }
        }

        //check settings
        $value = ini_get('allow_url_fopen');
        if($value!=='1') {
            $this->_optionsWrong[]= 'allow_url_fopen - have to be enabled';
        }
        $value = ini_get('disable_functions');
        if(!empty($value)) {
            $this->_optionsWrong[]= "disable_functions - have to be disabled";
        }
        $value = ini_get('safe_mode');
        if(!empty($value)) {
            $this->_optionsWrong[]= 'safe_mode - have to be disabled';
        }

        //create folder
        $base = realpath('..').'/';
        if (!@mkdir($base.$this->path)) {
            //$makedir = 'Can\'t create folder. Please, check permissions.';
        }

        //Go to next step or show errors
        if (empty($this->_phpVersion) && empty($this->_optionsWrong) && empty($this->_extensionsMissed) && !isset($makedir)) {
        	$this->nextStep(2, 'Installation package is downloading', 10, 1);
        } else {
            $msg = '';
            $msg .= $this->_phpVersion;
            if (!empty($this->_extensionsMissed)) {
                $msg .= '<br/>The following PHP extensions are not installed on your server:<ul>';
                foreach ($this->_extensionsMissed as $ext) {
                    $msg .= '<li>'.$ext.'</li>';
                }
                $msg .= '</ul>';
            }

            if (!empty($this->_optionsWrong)) {
                $msg .= '<br/>The following PHP extensions are not installed on your server:<ul>';
                foreach ($this->_optionsWrong as $opt) {
                    $msg .= '<li>'.$opt.'</li>';
                }
                $msg .= '</ul>';
            }
            if (!empty($makedir)) $msg .= $makedir;

            $this->err($msg);
        }
    } //end step1()

    /*
     * Step 2. Downloading intallation package
     *
     * @return void
     */
    public function step2() {
    	$base = realpath('..').'/';
    	if (!file_exists("{$base}{$this->path}/package.zip") ) { //|| (file_exists("{$base}{$this->path}/package.zip") && !file_exists("{$base}{$this->path}/package.zip.size"))
            $src = fsockopen("orbitopenadserver.com", 80, &$errno, &$errstr, 30);
            fputs($src,"GET /download/15b6bfd647071931b9657f5c08eb4255b349258cae2297d79f9912c0085d64cf HTTP/1.0\nHOST: orbitopenadserver.com\n\n");
            $dst = fopen("{$base}{$this->path}/package.zip","wb");

            //Get HTTP headers
            $head ='';
            while(!feof($src) && "\r\n" != ($head_str = fgets($src,2048))) {
                $head .= $head_str;
            }

            //Parse headers
            $head = explode("\n", $head);
            foreach ($head as $str) {
                $param = explode(': ', $str);
                $headers[$param[0]] = isset($param[1]) ? $param[1] : '';
            }

            //Get total file length
            $lenf = fopen("{$base}{$this->path}/package.zip.size","wb");
            fwrite($lenf, $headers['Content-Length']);
            fclose($lenf);

            // Load file
            while(!feof($src)) {
                //download and save file by peer per 65535b
               $buf = fread($src, 65535);
               fwrite($dst, $buf);
            }
    	} else {
            while (file_exists("{$base}{$this->path}/package.zip.size")) sleep(5);
    	}
        @fclose($src);
        @fclose($dst);
        sleep(2);
        @unlink("{$base}{$this->path}/package.zip.size");
        $this->nextStep(3, 'Unpacking files', 60);
    } //end step2()

    /*
     * Unpack package
     *
     * @return void
     */
    public function step3() {
        $base = realpath('..').'/';
        //unpack archive
        $zip = new ZipArchive;
        $res = $zip->open("{$base}{$this->path}/package.zip");
        if ($res === TRUE) {
            $zip->extractTo("{$base}{$this->path}");
            $zip->close();
            //move files and folders
            $this->dircpy("{$base}{$this->path}", "/openadserver", "", true);
            $this->rrmdir("{$base}{$this->path}/openadserver");
            $this->nextStep(4, 'Orbit Open Ad Server adjustment', 75);
        } else {
             $this->err("The archive {$base}{$this->path}/package.zip can’t be found.");
        }
    } //end step3()

    /*
     * Step 4. Configure Orbit Open Ad Server
     *
     * @return void
     */
    public function step4() {
        $this->mysql_init();
        $this->create_tables();
        $this->populate_tables();
        $this->create_admin_account();
        $this->database_config();
        $this->codeigniter_config();
        $this->create_admarket_account();

        $this->nextStep(5, 'Removing temporary files', 90);
   } //end step4()

    /*
     * Step 5. Remove tmp files and folders
     *
     * @return void
     */
    public function step5() {
        $base = realpath('..').'/';
        $this->rrmdir("{$base}{$this->path}/install");
        //change permissions
        foreach ($this->_permissions as $folder) {
            $subfolders = $this->dir_list("{$base}{$this->path}/{$folder}", "{$base}{$this->path}/{$folder}");
            chmod("{$base}{$this->path}/{$folder}", 0777);
            foreach($subfolders as $sub) {
                chmod("{$base}{$this->path}/{$folder}/{$sub}", 0777);
            }
        }
        sleep(1);
        $this->complete();
    }
   /**
    * Connect to MySQL
    *
    * @return void
    */
   public function mysql_init() {
      $this->db = @mysql_connect($this->dbhost, $this->dbuser, $this->dbpassword);
      if (!$this->db) {
        $this->err("Could not connect to database: ".mysql_error()."\n");
      }
      $result = mysql_query("USE `$this->dbname`");
      if (!$result) {
         $this->err("Could not use database: ".mysql_error($this->db)."\n");
      }
   } //end database_connect()

   /**
    * Run queries from .sql file
    * @param string $filename file name
    * @return void
    */
   protected function execute_queries_from_file($filename) {
      $base = realpath('..').'/';
      $file = @file_get_contents("{$base}{$this->path}/install/{$filename}.sql");
      $file = str_replace("\r", '', $file);
      $queries = explode(";\n", $file);
      foreach ($queries as $query) {
         if ($query == "" || $query == "\n") continue;
         $result = mysql_query($query,$this->db);
         if (!$result) {
           $this->err("Could not execute query: ".mysql_error($this->db)."\n");
         }
      }
   } //end execute_queries_from_file()

   /**
    * Create tables structure
    */
   protected function create_tables() {
      $this->execute_queries_from_file("structure");
   } //end create_tables()

   /**
    * Fill tables
    */
   protected function populate_tables() {
      $this->execute_queries_from_file("data");
   } //end populate_tables()

   /**
    * Create admin account and set API key
    * @return void
    */
   protected function create_admin_account() {
      $result = mysql_query("UPDATE entities SET e_mail='{$this->email}', password=MD5('{$this->password}') WHERE id_entity=1", $this->db);
      $result &= mysql_query("UPDATE settings SET value='{$this->email}' WHERE name='SystemEMail'", $this->db);
      $result &= mysql_query("UPDATE settings SET value='{$this->key}' WHERE name='ApiKey'", $this->db);
      if (!$result) {
         $this->err("Could not create admin account: ".mysql_error());
      }
   } //end create_admin_account()

   /**
    * Change config entry
    * @param string $text file configuration name
    * @param string $name param name
    * @param string $value new value
    */
   protected function change_config_item(&$text, $name, $value) {
        $text = preg_replace('/(\[\''.$name.'\'\][\s]*=[\s]*")([^"]*)("[\s]*;)/', "\${1}$value\${3}", $text);
   } //end change_config_item()

   /**
    * Remove redirect to installer
    * @param string &$text config file content
    */
   protected function change_install_to_index(&$text) {
      $text = preg_replace("~\nheader\('Location: install/install.php'\);\nexit;\n~","", $text);
   } //end change_config_item()


   /**
    * Modify configuration file
    * @param string $file path to config file
    * @param string $name config file name
    * @param array $data values (name => value)
    */
   protected function change_config_file($file, $name, $data) {
      $text = @file_get_contents($file);
      if ($text === FALSE) {
        $this->err("Could not read $name config file.");
      }
      foreach ($data as $key => $value) {
         $this->change_config_item($text, $key, $value);
      }
      $this->change_install_to_index($text);
      $result = @file_put_contents($file, $text);
      if ($result === FALSE) {
         $this->err("Could not write $name config file.");
      }
   } //end change_config_file()

   /**
    * Modify database file (CodeIginiter)
    */
   protected function database_config() {
      $base = realpath('..').'/';
      $this->change_config_file(
         "{$base}{$this->path}/application/config/database.php",
         "database",
         array(
            "hostname" => $this->dbhost,
            "username" => $this->dbuser,
            "password" => $this->dbpassword,
            "database" => $this->dbname
         )
      );
   } //end database_config()

   /**
    * Modify main config file (CodeIginiter)
    */
   protected function codeigniter_config() {
      $base = realpath('..').'/';
      $this->change_config_file(
         "{$base}{$this->path}/application/config/config.php",
         "CodeIgniter",
         array(
            "base_url" => site_url().'/'.$this->path.'/'
         )
      );
   } //end codeigniter_config()

   /**
    * Create new account in ByteCity
    */
   protected function create_admarket_account() {
      if ($login == "") {
         return;
      }
      $domain = parse_url(site_url(), PHP_URL_HOST);

      $postdata = http_build_query(
         array(
            'email' => $login,
            'password' => $pasword,
            'site_url' => site_url().'/'.$this->path.'/',
            'site_name' => $domain
         ));
      $opts = array(
         'http' => array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata));
      $context = stream_context_create($opts);
      $result_json = @file_get_contents($this->byte_city, false, $context);
      if ($result_json === FALSE) {
         return;
      }
      $result = json_decode($result_json);
      if($result->status == "error"){
         return;
      }else{
         $flag = mysql_query("UPDATE feeds SET affiliate_id_1='".$result->data."' WHERE name='bytecity'");
         if($flag === FALSE){
         }
      }
   } //end create_admarket_account()

    /*
     * Progress bar updater for step2.
     *
     * @return void
     */
    public function progress2() {
        $base = realpath('..').'/';
        //Get total file size
        if (file_exists("{$base}{$this->path}/package.zip.size")) {
            $lenf = @fopen("{$base}{$this->path}/package.zip.size","r");
            $len = @fgets($lenf, 2048);
            @fclose($lenf);
            //show
            $perc = $len/40;
            $percT = $len/100;
            $fsize = filesize("{$base}{$this->path}/package.zip");
            if ((int)$fsize != (int)trim($len)) {
                $this->size('Installation package is downloading ('.(round($fsize/1048576, 2).'Mb из '.round($len/1048576, 2).'Mb) ...'),round(10 + $fsize/$perc));
            } else {
                //stop
                @unlink("{$base}{$this->path}/package.zip.size");
                $this->size('','',true);
            }
        } else {
            $this->size('Installation package is downloading ...',0);
        }
        die();
    }

    public function err($msg = '') {
        echo json_encode(array('status' => 'error', 'description' => $msg));
        //echo $msg;
        die();
    }

    public function nextStep($step, $desc, $perc, $progress = 0) {
        echo json_encode(array('status' => 'incomplete', 'step' => $step, 'description' => $desc, 'width' => $perc, 'progress' => $progress));
        die();
    }

    public function size($desc, $perc, $stop=false) {
        echo json_encode(array(
                'description' => $desc,
                'width' => $perc,
                'stop' => ($stop) ? 1 : 0)
            );
        die();
    }

    public function complete() {
        echo json_encode(array('status' => 'complete', 'description' => 'The installation is complete. Now you will be redirected, please wait...', 'width' => 100, 'progress' => 0, 'link' => site_url().'/wp-admin/plugins.php?page=manage_ads&conf_step=STEP3_2'));
        die();
    }

    public function invalidStep() {
        $this->err('Invalid step');
    }

     /* 
      * copy a directory and all subdirectories and files (recursive)
      * void dircpy( str 'source directory', str 'destination directory' [, bool 'overwrite existing files'] )
      */
   private function dircpy($basePath, $source, $dest, $overwrite = false){
       if(!is_dir($basePath . $dest)) {//Lets just make sure our new folder is already created. Alright so its not efficient to check each time... bite me
            mkdir($basePath . $dest);
       }
       if($handle = opendir($basePath . $source)){        // if the folder exploration is sucsessful, continue
           while(false !== ($file = readdir($handle))){ // as long as storing the next file to $file is successful, continue
               if($file != '.' && $file != '..'){
                   $path = $source . '/' . $file;
                   if(is_file($basePath . $path)){
                       if(!is_file($basePath . $dest . '/' . $file) || $overwrite)
                       if(!@copy($basePath . $path, $basePath . $dest . '/' . $file)){
                           echo '<font color="red">File ('.$path.') could not be copied, likely a permissions problem.</font>';
                       }
                   } elseif(is_dir($basePath . $path)){
                       if(!is_dir($basePath . $dest . '/' . $file))
                       mkdir($basePath . $dest . '/' . $file); // make subdirectory before subdirectory is copied
                       $this->dircpy($basePath, $path, $dest . '/' . $file, $overwrite); //recurse!
                   }
               }
           }
           closedir($handle);
       }
   }
    
    /*
     * Get files list
     *
     * @param string $path path to folder
     * @param string $path same as previos (using for recursion)
     */
    private function dir_list($path, $basepath) {
        $list = scandir($path);
                unset($list[0]);
                unset($list[1]);
        foreach ($list as $id => $item) {
            if (is_dir($path.'/'.$item)) {
                $dir_list = $this->dir_list($path.'/'.$item, $basepath);
                foreach ($dir_list as $one) {
                        $list[]=$one;
                }
                unset($list[$id]);
            } else {
                $list[$id] = $path.'/'.$item;
            }
        }
        foreach ($list as $id => $item) {
        $list[$id] = str_replace($basepath.'/','',$item);
        }
        return $list;
    }

    /*
     * Recursive deleting
     *
     * @param string $dir path to directory
     * @return void
     */
    private function rrmdir($dir) {
        if (@is_dir($dir)) {
         $objects = @scandir($dir);
         foreach ($objects as $object) {
           if ($object != "." && $object != "..") {
             if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else @unlink($dir."/".$object);
           }
         }
         @reset($objects);
         @rmdir($dir);
        }
    }
}
