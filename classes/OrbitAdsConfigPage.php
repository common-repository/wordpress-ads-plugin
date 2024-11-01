<?php
/**
 * Orbit Open Ad Server Config Page Class
 *
 * @package     Orbit Open Ad Server
 * @subpackage  extension
 * @category    Wordpress
 * @author      OrbitScripts LLC
 */

class OrbitAdsConfigPage {

    const RIGHT_DELIMITER = '%>';
    const LEFT_DELIMITER = '<%';

    private  $content = '';

    private $config = null;

    public function  __construct() {
        $this->orbitHost = new OrbitHostingApi();
        $this->orbitApi = new OrbitOpenAdServerApi();
        $this->config = get_option('orbitscriptsads_config');
        $this->header();
    }

    public function header() {
        $this->content .= '<div class="wrap">';
        $this->content .= '<div id="icon-plugins" class="icon32"><br></div>';
    }

    public function  step1() {
        $domain = isset($_POST['domain']) ? $_POST['domain'] : '';
        $path = isset($_POST['path']) ? $_POST['path'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : get_option('admin_email');
        $passwd = isset($_POST['passwd']) ? $_POST['passwd'] : '';

        $key = isset($_POST['key']) ? $_POST['key'] : '';
        $host = isset($_POST['host']) ? $_POST['host'] : '';
        if (isset($_POST['action'])) {
            $err_domain = $err_email = $err_passwd = $err_email_2 = $err_passwd_2 = $err_path = $err_main = $err_host = $err_key ='';
            switch ($_POST['action']) {
                case 'opt1':
                    $ok = true;
                    if (!$this->orbitHost->checkDomain($domain)) {
                        $ok = false;
                        $err_domain = $this->orbitHost->getMsg();
                    }
                    if (!$this->orbitHost->checkEmail($email)) {
                        $ok = false;
                        $err_email = $this->orbitHost->getMsg();
                    }
                    if (!$this->orbitHost->checkPassword($passwd)) {
                        $ok = false;
                        $err_passwd = $this->orbitHost->getMsg();
                    }
                    break;
                case 'opt2':
                    $ok = true;
                    if (empty($path)) {
                        $ok = false;
                        $err_path = 'Path can\'t be empty.';
                    } else {
                        if (0 == preg_match('~^[a-zA-z0-9]{1}[0-9a-zA-z-_]*$~',$path)) {
                            $ok = false;
                            $err_path = 'Invalid path.';
                        } else {
                            if (file_exists(WP_PLUGIN_DIR.'/../../'.$path.'/')) {
                                //TODO: uncommet
                                //$ok = false;
                                //$err_path = 'Error: This path is already exist.';
                            }
                        }
                    }
                    if (!$this->orbitHost->checkEmail($email)) {
                        $ok = false;
                        $err_email_2 = $this->orbitHost->getMsg();
                    }
                    if (!$this->orbitHost->checkPassword($passwd)) {
                        $ok = false;
                        $err_passwd_2 = $this->orbitHost->getMsg();
                    }
                    break;
                case 'opt3':
                    $ok = true;
                    if (strlen($key) == 0) {
                        $ok = false;
                        $err_key = "API key isn't set";
                    } elseif (32 != strlen($key)) {
                        $ok = false;
                        $err_key = "API key too short";
                    }
                    if ($host) {
                        $purl = parse_url($host);
                        $port = (!empty($purl['port'])) ? $purl['port'] : 80;
                        $prefix = ($purl['scheme'] == 'https') ? 'ssl://' : '';
                        $fp = @fsockopen ($prefix.$purl['host'], $port, $errno, $errstr, 30);
                        if (!$fp) {
                            $ok = false;
                            $err_host = "$errstr ($errno)";
                        } else {
                            fclose ($fp);
                            $this->orbitApi->init(array('url'=>$host, 'key'=>$key));
                            if (!$this->orbitApi->testConnection()) {
                                $ok = false;
                                $err_main = $this->orbitApi->getLastError();
                            }
                        }
                    } else {
                            $ok = false;
                            $err_host = "Url isn't set";
                    }
                    break;
                default:
                    break;
            }
        }

        //option 1
        if ((isset($_POST['action']) && $_POST['action'] == 'opt1') && $ok === true) {
            if ($this->orbitHost->requestAccount($domain, $email, $passwd)) {
                $this->config['step'] = 'STEP2_1';
                $this->config['domain'] = $domain;
                $this->config['email'] = $email;
                $this->config['password'] = $passwd;

                update_option('orbitscriptsads_config', $this->config);
                $this->step2_1();
            } else {
                $err_main='Error: '.$this->orbitHost->getMsg();
            }
        }

        //option 2
        if ((isset($_POST['action']) && $_POST['action'] == 'opt2') && $ok === true) {
            $this->config['step'] = 'STEP2_2';
            $this->config['path'] = $path;
            $this->config['email'] = $email;
            $this->config['password'] = $passwd;

            update_option('orbitscriptsads_config', $this->config);
            $this->step2_2();
        }

        //option 3
        if ((isset($_POST['action']) && $_POST['action'] == 'opt3') && $ok === true) {
                $this->config['step'] = 'STEP3_3';
                $this->config['key'] = $key;
                $this->config['url'] = $host;
                update_option('orbitscriptsads_config', $this->config);
                $this->step3_3();
        }

        //show page
        if (!isset($_POST['action']) || $ok === false) {
            $this->content .= '<h2>Orbit Open Ad Server Configuration Master</h2>';
            $this->content .= '<ul class="steps">
                                    <li class="passed"><em>Step 1</em>Install and activate Orbit Open Ad Server plugin.<span class="arrow"></span></li>
                                    <li class="current"><em>Step 2</em>Configure and connect Orbit Open Ad Server<span class="arrow"></span></li>
                                    <li><em>Step 3</em>Publish you ad channels on the site<span class="arrow"></span></li>
                               ';
            $this->content .= '<div><h2>Have a questions?</h2><p>Please, visit <a href="http://orbitopenadserver.com/forum/">Orbit Open Ad Server Community</a>.</p></div></ul>';

            //Option 1 Form
            //$this->content .= '<h3>STEP1</h3><span class="error">'.$err_main.'</span>';
            $this->content .= '<div class="clear"></div><p>Installed plug-in is a part of a free open source product - Orbit Open Ad Server that provides your
            advertisers an ability to successfully manage their ads and campaigns, and you will earn revenue from displaying ads on your websites. 
            Please review powerful features and abilities of Orbit Open Ad Server right now.</p>
			       <p>Using WordPress Ads Plug-in you can easily display ads on your blog.</p>
			       <p>To get started you need to connect WordPress Ads Plug-in to the Orbit Open Ad Server.
			       Below you may find several simple ways of Orbit Open Ad Server software installation:</p>';
	    $this->content .= '<div class="accordion">';
            $this->content .= '<h3>Option 1. Quickly start using Orbit Open Ad Server on our demo hosting for free*. </h3>
                                    <div class="content first  '.((isset($_POST['action']) && $_POST['action'] == 'opt3') ? 'active' : 'inactive').'">
                                        <p>You can to get ready for using Orbit Open Ad Server on our free hosting server*. </p>
                                        <p>After the trial period of 30 days you can keep using the application however we reserve the right to show up to 10% of our advertisement on your websites.  Or you can easily transfer the application to your own server with all the data.</p>
                                        <form id="orbitadsconfig_opt1" action="" method="POST" class="validate">
                                            <p class="howto">After period of 30 days we reserve the right to show up to 10% of our advertisement on your websites.</p>
                                                <input type="hidden" value="orbitscripts_config_page" name="page"/>
						<input type="hidden" value="opt1" name="action"/>
						<table class="form-table">
							<tr valign="top" class="form-field form-required '.(!empty($err_email) ? 'form-invalid' : '').'">
								<th scope="row">
									<label for="opt1_email">Email
									<span class="description">(required)</span></label>

								</th>
								<td>
									<input class="regular-text" id="opt1_email" name="email" type="text" value="'.$email.'"/>
                                                                        <span class="error-text">'.$err_email.'</span>

								</td>
							</tr>
							<tr class="form-field form-required '.(!empty($err_passwd) ? 'form-invalid' : '').'">
								<th scope="row">
									<label for="opt1_pass1">Password
									<span class="description">(twice, required)</span></label>

								</th>
								<td>
									<input class="regular-text passwd" name="passwd" type="password" id="opt1_pass1" autocomplete="off" value="'.$passwd.'"/>
                                                                            <span class="error-text">'.$err_passwd.'</span>
									<br/>
									<input class="regular-text passwd" name="passwd2" type="password" id="opt1_pass2" autocomplete="off" value="'.$passwd.'"/>
									<br/>
									<div id="pass-strength-result" class="pass-strength-result">Strength indicator</div>
									<p class="description indicator-hint">Hint: The password should be at least six characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).</p>

								</td>
							</tr>
							<tr valign="top" class="form-field form-required '.(!empty($err_domain) ? 'form-invalid' : '').'">
								<th scope="row">
									<label for="opt1_domain">Domain name
									<span class="description">(required)</span></label>
								</th>
								<td>
									<input class="regular-text" id="opt1_domain" name="domain" type="text" value="'.$domain.'"/><span class="note">.orbitopenadserver.com</span>
                                                                        <span class="error-text">'.$err_domain.'</span>
									<br/>
									<span class="description">the Latin alphabet, numerals, shouldn’t start with -</span>
								</td>
							</tr>
						</table>
						<p class="submit">
                                                    <input class="button-primary" id="opt1_submit" type="submit" value="Launch"/>
						</p>

					</form>
				</div>';

             //Option 2 Form
             $this->content .= '<h3>Option 2. Install Orbit Open Ad Server to your blog folder.</h3>
				<div class="content second  '.((isset($_POST['action']) && $_POST['action'] == 'opt2') ? 'active' : 'inactive').'">
					<p>Orbit Open Ad Server will be automatically downloaded, unpacked and configured on your server. You only need to specify the path where you want to install it.</p>
					<p>All actions will be passed automatically. Fill form fields and click "Install Now".</p>
					<form id="orbitadsconfig_opt2" action="" method="POST" class="validate">

						<input type="hidden" value="orbitscripts_config_page" name="page"/>
						<input type="hidden" value="opt2" name="action"/>
						<table class="form-table">
							<tr valign="top" class="form-field form-required '.(!empty($err_email_2) ? 'form-invalid' : '').'">
								<th scope="row">
									<label for="opt2_email">Email
									<span class="description">(required)</span></label>
								</th>
								<td>

									<input class="regular-text" id="opt2_email" name="email" type="text" value="'.$email.'"/>
                                                                        <span class="error-text">'.$err_email_2.'</span>
								</td>
							</tr>
							<tr class="form-field form-required '.(!empty($err_passwd_2) ? 'form-invalid' : '').'">
								<th scope="row">
									<label for="opt2_pass1">Password
									<span class="description">(twice, required)</span></label>
								</th>
								<td>

									<input class="regular-text passwd" name="passwd" type="password" id="opt2_pass1" autocomplete="off" />
                                                                        <span class="error-text">'.$err_passwd_2.'</span>
									<br/>
									<input class="regular-text passwd" name="passwd2" type="password" id="opt2_pass2" autocomplete="off" />
									<br/>
									<div id="pass-strength-result" class="pass-strength-result">Strength indicator</div>
									<p class="description indicator-hint">Hint: The password should be at least six characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).</p>
								</td>

							</tr>
							<tr valign="top" class="form-field form-required '.(!empty($err_path) ? 'form-invalid' : '').'">
								<th scope="row">
									<label for="opt2_path">Path
									<span class="description">(required)</span></label>
								</th>
								<td>
									<input class="regular-text" id="opt2_path" name="path" type="text" value="'.$path.'"/>
                                                                        <span class="error-text">'.$err_path.'</span>
									<br/>
									<span class="description">only numbers and latin symbols</span>
								</td>
							</tr>
						</table>
						<p class="submit">
						<input class="button-primary" id="opt2_submit" type="submit" value="Install now"/>

						</p>
					</form>
				</div>';


            //Option 3 Form
             $this->content .= '<h3>Option 3.  You can install Orbit Open Ad Server manually or connect it to already installed application.</h3>
				<div class="content third '.((isset($_POST['action']) && $_POST['action'] == 'opt3') ? 'active' : 'inactive').'">
					<p>If you would like to install Orbit Open Ad Server yourself you can download installation package of Orbit Open Ad Server and follow the instructions on how to install it on any computer or server. After the installation is completed please specify where you installed it and API key.</p>
					<p>If you already have previously installed Orbit Open Ad Server and would like to connect it with certain blog, just specify where you installed Orbit Open Ad Server and API key of your Orbit Open Ad Server.</p>

					<form id="orbitadsconfig_opt3" action="" method="POST" class="validate">
						<input type="hidden" value="orbitscripts_config_page" name="page"/>
						<input type="hidden" value="opt3" name="action"/>
						<table class="form-table">
							<tr valign="top" class="form-field form-required '.(!empty($err_host) ? 'form-invalid' : '').'">
								<th scope="row">
									<label for="opt3_host">Orbit Open Ad Server URL
									<span class="description">(required)</span></label>
								</th>

								<td>
									<input class="regular-text" id="opt3_host" name="host" type="text" value="'.$host.'"/>
                                                                        <span class="error-text">'.$err_host.'</span>
								</td>
							</tr>
							<tr valign="top" class="form-field form-required '.(!empty($err_key) ? 'form-invalid' : '').'">
								<th scope="row">
									<label for="opt3_key">API key
									<span class="description">(required)</span></label>
								</th>

								<td>
									<input class="regular-text" id="opt3_key" name="key" type="text" value="'.$key.'"/>
                                                                        <span class="error-text">'.$err_key.'</span>
								</td>
							</tr>
						</table>
						<p class="submit">
                                                    <input class="button-primary" id="opt3_submit" type="submit" value="Save"/>
						</p>
					</form>

				</div>';
              $this->content .= "</div>
                  <script type='text/javascript'>
			/* <![CDATA[ */
			var commonL10n = {
				warnDelete: 'You are about to permanently delete the selected items. \'Cancel\' to stop, \'OK\' to delete.'
			};
			try{convertEntities(commonL10n);}catch(e){};
			var wpAjax = {
				noPerm: 'You do not have permission to do that.',
				broken: 'An unidentified error has occurred.'
			};
			try{convertEntities(wpAjax);}catch(e){};
			var pwsL10n = {
				empty: 'Strength indicator',
				short: 'Very weak',
				bad: 'Weak',
				good: 'Medium',
				strong: 'Strong',
				mismatch: 'Mismatch'
			};
			try{convertEntities(pwsL10n);}catch(e){};

                        (function($){
				function pass_strength_check() {
					var pass1 = $(this).val(), pass2 = $(this).siblings('.passwd').val(), strength;

					$(this).siblings('.pass-strength-result').removeClass('short bad good strong');
					if ( ! pass1 ) {
						$(this).siblings('.pass-strength-result').html( pwsL10n.empty );
						return;
					}

					strength = passwordStrength(pass1, '123', pass2);
					switch ( strength ) {
						case 2:
							$(this).siblings('.pass-strength-result').addClass('bad').html( pwsL10n['bad'] );
							break;
						case 3:
							$(this).siblings('.pass-strength-result').addClass('good').html( pwsL10n['good'] );
							break;
						case 4:
							$(this).siblings('.pass-strength-result').addClass('strong').html( pwsL10n['strong'] );
							break;
						case 5:
							$(this).siblings('.pass-strength-result').addClass('short').html( pwsL10n['mismatch'] );
							break;
						default:
							$(this).siblings('.pass-strength-result').addClass('short').html( pwsL10n['short'] );
					}
				}

				$( function($) {
					$('.passwd').val('').keyup( pass_strength_check );
				})
                        })(jQuery);
                        if(typeof wpOnload=='function')wpOnload();
			/* ]]> */</script>";
        }
    }

    public function  step2_1() {
        $this->content = '<h2>Orbit Open Ad Server Configuration Master</h2>';
	$this->content .= '<ul class="steps">
				<li class="passed"><em>Step 1</em>Install and activate Orbit Open Ad Server plugin.<span class="arrow"></span></li>
				<li class="current"><em>Step 2</em>Configure and connect Orbit Open Ad Server<span class="arrow"></span></li>
				<li><em>Step 3</em>Publish you ad channels on the site<span class="arrow"></span></li>
			   ';
        $this->content .= '<div><h2>Have a questions?</h2><p>Please, visit <a href="http://orbitopenadserver.com/forum/">Orbit Open Ad Server Community</a>.</p></div></ul>';
        $code = (isset($_POST['code'])) ? $_POST['code'] : '';
        $ok = true;
        if ($code) {
            if ($this->orbitHost->activateAccount($this->config['domain'], $code)) {
                $api_key = $this->orbitHost->getMsg();
                if (strlen($api_key) != 32) {
                    $ok = false;
                } else {
                    $domain = $this->config['domain'];
                    //flush config
                    unset ($this->config);
                    //save
                    $this->config['key'] = $api_key;
                    $this->config['url'] = 'http://'.$domain.'.orbitopenadserver.com';
                    update_option('orbitscriptsads_config', $this->config);

                    $this->step3_1();
                }
            } else {
                $ok = false;
            }
        }

        if (!$code || !$ok) {
            if (!$ok) echo 'Error: '.$this->orbitHost->getMsg();
            $this->content .= '<p>Your account has been successfully created! Please activate it now.</p>';
            $this->content .= '<p>We sent you an e-mail with activation code. Please copy the code and paste it into the field below:</p>';
            $this->content .= '<form id="orbitadsconfig_opt1_2" action="" method="POST">
                                       <input type="hidden" value="orbitscripts_config_page" name="page">
                                       <input type="hidden" value="opt1_2" name="action">
                                       <p><label for="opt1_2_code">'. __('Activation code:') .
                                            '<input id="opt1_2_code" name="code" type="text" value="'.$code.'"/>
                                        </label></p>
                                        <input id="opt1_2_submit" type="submit" value="Activate"/>
                                    </form>';
        }
    }

    public function  step2_2() {
        $this->content .= '<h2>Orbit Open Ad Server Configuration Master</h2>';
	$this->content .= '<ul class="steps">
				<li class="passed"><em>Step 1</em>Install and activate Orbit Open Ad Server plugin.<span class="arrow"></span></li>
				<li class="current"><em>Step 2</em>Configure and connect Orbit Open Ad Server<span class="arrow"></span></li>
				<li><em>Step 3</em>Publish you ad channels on the site<span class="arrow"></span></li>
			   ';
        $this->content .= '<div><h2>Have a questions?</h2><p>Please, visit <a href="http://orbitopenadserver.com/forum/">Orbit Open Ad Server Community</a>.</p></div></ul>';
        $this->content .= '<h3>Orbit Open Ad Server installation is in progress...</h3>';
        $this->content .= '<small>Please do not refresh the page or press “back” button until the installation is complete.</small>';
        $this->content .= '<div id="orbitscriptsads_install">
			<div class="meter animate">
				<span style="width: 0;"><span><strong>0%</strong></span></span>
			</div>
		</div>';
	$this->content .= '<script type="text/javascript">
				jQuery(function($){
					orbitscriptsads_install()
					orbitscriptsads_progress_animate_helper()
				})
			    </script>';
        $this->content .= '<div id="orbitscriptsads_install_txt">Server configuration checking...</div>';
    }

    public function  step2_2_error() {
        $this->content .= '<h2>Orbit Open Ad Server Configuration Master</h2>';
	$this->content .= '<ul class="steps">
				<li class="passed"><em>Step 1</em>Install and activate Orbit Open Ad Server plugin.<span class="arrow"></span></li>
				<li class="current"><em>Step 2</em>Configure and connect Orbit Open Ad Server<span class="arrow"></span></li>
				<li><em>Step 3</em>Publish you ad channels on the site<span class="arrow"></span></li>
			   ';
        $this->content .= '<div><h2>Have a questions?</h2><p>Please, visit <a href="http://orbitopenadserver.com/forum/">Orbit Open Ad Server Community</a>.</p></div></ul>';
        $this->content .= '<h3>Oops!</h3>
                           <p>Unfortunately Plug In failed to connect to your Orbit Open Adserver.  Please make sure you entered URL and API key correctly.  If you entered everything correctly and it still does not work please contact us and we’ll be happy to look at the issue.
                           ';
    }

    public function  step3_1() {
        $this->content = '<h2>Orbit Open Ad Server Configuration Master</h2>';
	$this->content .= '<ul class="steps">
				<li class="passed"><em>Step 1</em>Install and activate Orbit Open Ad Server plugin.<span class="arrow"></span></li>
				<li class="passed"><em>Step 2</em>Configure and connect Orbit Open Ad Server<span class="arrow"></span></li>
				<li class="current"><em>Step 3</em>Publish you ad channels on the site<span class="arrow"></span></li>
			   ';
        $this->content .= '<div><h2>Have a questions?</h2><p>Please, visit <a href="http://orbitopenadserver.com/forum/">Orbit Open Ad Server Community</a>.</p></div></ul>';
        $this->config['step'] = 'STATUS';

        $this->orbitApi->init(array('url'=>$this->config['url'], 'key'=>$this->config['key']));
        $this->config['site'] = $this->orbitApi->createSite(parse_url(home_url(), PHP_URL_HOST), get_option('blogname'), get_option('blogdescription'));
        $this->config['status'] = true;
        $this->content .= '<h3>Congratulations! Orbit Open Ad Server adjustment is successfully done.</h3>';
        $this->content .= '<p>Your Orbit Open Ad Server is placed on our server and ready for the start. Below you may find some useful information:</p>';
        $this->content .= '<p><b>The Administrator Panel:</b> '.$this->config['url'].'/admin</p>';
        $this->content .= '<p><b>The Advertiser Panel:</b> '.$this->config['url'].'/advertiser</p>';

        update_option('orbitscriptsads_config', $this->config);
    }
    
    public function  step3_2() {
        $this->content .= '<h2>Orbit Open Ad Server Configuration Master</h2>';
	$this->content .= '<ul class="steps">
				<li class="passed"><em>Step 1</em>Install and activate Orbit Open Ad Server plugin.<span class="arrow"></span></li>
				<li class="passed"><em>Step 2</em>Configure and connect Orbit Open Ad Server<span class="arrow"></span></li>
				<li class="current"><em>Step 3</em>Publish you ad channels on the site<span class="arrow"></span></li>
			   ';
        $this->content .= '<div><h2>Have a questions?</h2><p>Please, visit <a href="http://orbitopenadserver.com/forum/">Orbit Open Ad Server Community</a>.</p></div></ul>';
        $this->config['step'] = 'STATUS';
        $this->config['url'] = site_url().'/'.$this->config['path'];

        $this->orbitApi->init(array('url'=>$this->config['url'], 'key'=>$this->config['key']));
        $this->config['site'] = $this->orbitApi->createSite(parse_url(home_url(), PHP_URL_HOST), get_option('blogname'), get_option('blogdescription'));
        $this->config['status'] = true;
        $this->content .= '<h3>Congratulations! Your Orbit Open Ad Server is successfully adjusted and installed.</h3>';
        $this->content .= '<p>Below you may find some useful information:</p>';
        $this->content .= '<p><b>The Administrator Panel:</b> '.$this->config['url'].'/admin</p>';
        $this->content .= '<p><b>The Advertiser Panel:</b> '. $this->config['url'].'/advertiser</p>';
        
        update_option('orbitscriptsads_config', $this->config);
    }

    public function  step3_3() {
        $this->content .= '<h2>Orbit Open Ad Server Configuration Master</h2>';
	$this->content .= '<ul class="steps">
				<li class="passed"><em>Step 1</em>Install and activate Orbit Open Ad Server plugin.<span class="arrow"></span></li>
				<li class="passed"><em>Step 2</em>Configure and connect Orbit Open Ad Server<span class="arrow"></span></li>
				<li class="current"><em>Step 3</em>Publish you ad channels on the site<span class="arrow"></span></li>
			   ';
        $this->content .= '<div><h2>Have a questions?</h2><p>Please, visit <a href="http://orbitopenadserver.com/forum/">Orbit Open Ad Server Community</a>.</p></div></ul>';
        $this->orbitApi->init(array('url'=>$this->config['url'], 'key'=>$this->config['key']));
        $this->config['site'] = $this->orbitApi->createSite(parse_url(home_url(), PHP_URL_HOST), get_option('blogname'), get_option('blogdescription'));
        $this->config['status'] = true;
        $this->config['step'] = 'STATUS';
        update_option('orbitscriptsads_config', $this->config);
    }

    public function  status() {
        $this->content .= '<h2>Ads Plugin Config</h2>';

        if (isset($_POST['url'])) {
            $this->config['palette'] = $_POST['pallete'];
            $this->config['url'] = $_POST['host'];
            $this->config['key'] = $_POST['key'];
            $this->content = '<div id="orbitscripts-warning" class="updated fade below-h2">
                     <p><strong>Saved.</strong></p></div>';
        }

        $this->orbitApi->init($this->config);
        if ($this->orbitApi->testConnection()) {
            $color = 'green';
            $msg = 'Connection with Orbit Open Ad Server was successfully established.';
            $this->config['status'] = true;
        } else {
            $color = 'red';
            $msg = 'Error: '.$this->orbitApi->getLastError();
            $this->config['status'] = false;
        }
        if ($palettes = $this->orbitApi->getPalletes()) {
            if (0 < count($palettes)) {
                foreach ($palettes as $id => $palette) {
                    $options = '<option value="'.$id.'" '.(( $this->config['palette'] == $id) ? 'selected=selected' : '').'>'.$palette['name'].'</option>';

                }
            }
        } else {
            $options = '<option value="0">Default</option>';
        }
        $this->content .= '<form id="orbitadsconfig" action="" method="POST" class="validate">
                                <input type="hidden" value="manage_ads" name="page"/>
                                <input type="hidden" value="save" name="action"/>
                                <table class="form-table">
                                        <tr valign="top" class="form-field form-required '.(!empty($err_host) ? 'form-invalid' : '').'">
                                                <th scope="row">
                                                        <label for="host">Orbit Open Ad Server URL
                                                        <span class="description">(required)</span></label>
                                                </th>

                                                <td>
                                                        <input class="regular-text" id="host" name="host" type="text" value="'.$this->config['url'].'"/>
                                                        <span class="error-text">'.$err_host.'</span>
                                                </td>
                                                <td rowspan="3">
                                                    <div style="padding: 20px; width: 230px; height: 45px; background-color: '.$color.'; color: white;">'.$msg.'</div>
                                                </td>
                                        </tr>
                                        <tr valign="top" class="form-field form-required '.(!empty($err_key) ? 'form-invalid' : '').'">
                                                <th scope="row">
                                                        <label for="key">API key
                                                        <span class="description">(required)</span></label>
                                                </th>

                                                <td>
                                                        <input class="regular-text" id="key" name="key" type="text" value="'.$this->config['key'].'"/>
                                                        <span class="error-text">'.$err_key.'</span>
                                                </td>
                                        </tr>
                                        <tr valign="top" class="form-field form-required '.(!empty($err_key) ? 'form-invalid' : '').'">
                                                <th scope="row">
                                                        <label for="pallete">Color pallete
                                                        <span class="description">(required)</span></label>
                                                </th>

                                                <td>
                                                        <select id="pallete" name="pallete">
                                                            '.$options.'
                                                        </select>
                                                        <span class="error-text">'.$err_key.'</span>
                                                </td>
                                        </tr>
                                </table>
                                <p class="submit">
                                    <input class="button-primary" id="opt3_submit" type="submit" value="Save"/>
                                </p>
                        </form>';
        update_option('orbitscriptsads_config', $this->config);
    }

    public function footer() {
        $this->content .= '</div>';
    }

    /**
     * Show content
     *
     * @return void
     */
    public function show() {
        $this->footer();
        echo $this->content;
    }
}
