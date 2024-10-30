<?php
/*
 * Plugin Name:   Blogrush Click Maximizer
 * Version:       1.2.4
 * Plugin URI:    http://www.maxblogpress.com/plugins/bcm/
 * Description:   BlogRush Click Maximizer allows you to control what you want to display in BlogRush. You can include or exclude any post you want and also use alternate - short and catchy title just for BlogRush. Adjust your settings <a href="options-general.php?page=blogrush-click-maximizer/blogrush-click-maximizer.php">here</a>.
 * Author:        MaxBlogPress
 * Author URI:    http://www.maxblogpress.com
 *
 * License:       GNU General Public License
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * Copyright (C) 2007 www.maxblogpress.com
 * 
 */
 
define('BCMAX_NAME', 'Blogrush Click Maximizer'); // Name of the Plugin
define('BCMAX_VERSION', '1.2.4');	              // Current version of the Plugin

/**
 * BCMax - Blogrush Click Maximizer Class
 * Holds all the necessary functions and variables
 */
class BCMax 
{
	/**
	 * Constructor. Adds Blogrush Click Maximizer plugin's actions/filters.
	 * @access public
	 */
	function BCMax() {
		global $wp_version;
		
		$this->bcm_path     = preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', __FILE__);
		$this->bcm_path     = str_replace('\\','/',$this->bcm_path);
		$this->bcm_siteurl  = get_bloginfo('wpurl');
		$this->bcm_siteurl  = (strpos($this->bcm_siteurl,'http://') === false) ? get_bloginfo('siteurl') : $this->bcm_siteurl;
		$this->bcm_fullpath = $this->bcm_siteurl.'/wp-content/plugins/'.substr($this->bcm_path,0,strrpos($this->bcm_path,'/')).'/';
		$this->bcm_abspath  = str_replace("\\","/",ABSPATH); 
		$this->img_how      = '<img src="'.$this->bcm_fullpath.'images/how.gif" border="0" align="absmiddle">';
		$this->img_comment  = '<img src="'.$this->bcm_fullpath.'images/comment.gif" border="0" align="absmiddle">';

	    add_action('activate_'.$this->bcm_path, array(&$this, 'bcmActivate'));
		add_action('admin_menu', array(&$this, 'bcmAddMenu'));
		$this->bcm_activate = get_option('bcm_activate');
		
		if ( $wp_version < 2.1 ) {
			add_action('simple_edit_form', array(&$this, 'bcmCustomFields'));
			add_action('edit_form_advanced', array(&$this, 'bcmCustomFields'));
		} else {
			add_action('dbx_post_advanced', array(&$this, 'bcmCustomFields'));
			add_action('dbx_page_advanced', array(&$this, 'bcmCustomFields'));
		}
		if ( $this->bcm_activate == 2 ) {
			add_action('edit_post', array(&$this, 'bcmEditMetaData'));
			add_action('save_post', array(&$this, 'bcmEditMetaData'));
			add_action('publish_post', array(&$this, 'bcmEditMetaData'));
		}
	}
	
	/**
	 * Called when plugin is activated.
	 * @access public
	 */
	function bcmActivate() {
		add_option('bcm_activate', 0);
		return true;
	}
	
	/**
	 * Adds "DiffPostsPerPage" link to admin Options menu
	 * @access public 
	 */
	function bcmAddMenu() {
		add_options_page('Blogrush Click Maximizer', 'Blogrush Click Maximizer', 'manage_options', $this->bcm_path, array(&$this, 'bcmOptionsPg'));
	}
	
	/**
	 * Displays the Options page
	 * @access public 
	 */
	function bcmOptionsPg() {
		$form_1 = 'bcm_reg_form_1';
		$form_2 = 'bcm_reg_form_2';
		// Activate the plugin if email already on list
		if ( trim($_GET['mbp_onlist']) == 1 ) { 
			$this->bcm_activate = 2;
			update_option('bcm_activate', $this->bcm_activate);
			$msg = 'Thank you for registering the plugin. It has been activated'; 
		} 
		// If registration form is successfully submitted
		if ( ((trim($_GET['submit']) != '' && trim($_GET['from']) != '') || trim($_GET['submit_again']) != '') && $this->bcm_activate != 2 ) { 
			update_option('bcm_name', $_GET['name']);
			update_option('bcm_email', $_GET['from']);
			$this->bcm_activate = 1;
			update_option('bcm_activate', $this->bcm_activate);
		}
		if ( intval($this->bcm_activate) == 0 ) { // First step of plugin registration
			$this->bcmRegister_1($form_1);
		} else if ( intval($this->bcm_activate) == 1 ) { // Second step of plugin registration
			$name  = get_option('bcm_name');
			$email = get_option('bcm_email');
			$this->bcmRegister_2($form_2,$name,$email);
		} else if ( intval($this->bcm_activate) == 2 ) { // Options page
			if ( $_GET['action'] == 'upgrade' ) {
				$this->bcmUpgradePlugin();
				exit;
			}
			$this->bcmShowOptionsPage($msg);
		}
	}
	
	/**
	 * Display the options page
	 * @access public 
	 */
	function bcmShowOptionsPage($msg=0) {
		if ( $msg ) {
			echo '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>';
		}
		?>
		<form name="bcmform" method="post">
		<div class="wrap"><h2><?php echo BCMAX_NAME.' '.BCMAX_VERSION; ?></h2>
		 <table align="center" cellspacing="1" cellpadding="3">
		  <tr>
		   <td><div align="center"><h3>Plugin has been activated!</h3></div></td>
		  </tr>
		</table>
		<p style="text-align:center;margin-top:3em;"><strong><?php echo BCMAX_NAME.' '.BCMAX_VERSION; ?> by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>
	    </div>
	    </form>
		<?php
	}
	
	/**
	 * Adds Blogrush Click Maximizer's custom fields
	 * @access public
	 */
	function bcmCustomFields() {
		global $wp_version;
		if ( isset($_REQUEST['post']) ) {
			$bcm_title     = get_post_meta($_REQUEST['post'], 'bcm_title');
			$bcm_title     = $bcm_title[0];
			$bcm_title_len = strlen(trim($bcm_title));
			$bcm_include   = get_post_meta($_REQUEST['post'], 'bcm_include');
			$bcm_include   = $bcm_include[0];
		} else {
			$bcm_include   = '';
			$bcm_title_len = 0;
		}
		$bcm_checked     = ($bcm_include!='false')?'checked':'';
		$bcm_site_url    = get_option('siteurl');
		$bcm_feed_url    = $bcm_site_url.'/wp-content/plugins/blogrush-click-maximizer/blogrush-feed.php';
		?>
		<script type="text/javascript">
		var maxlength = 40;
		function theCounter(title, counter) {
			if (title.value.length > maxlength) {
				title.value = title.value.substring(0, maxlength);
			} else {
				counter.innerHTML = title.value.length;
			}
		}
		</script>
		<?php if ( $wp_version >= 2.5 ) { ?>
			<div id="trackbacksdiv22" class="postbox closed">
			<h3><a class="togbox">+</a> <?php echo BCMAX_NAME.' '.BCMAX_VERSION;?></h3>
			<div class="inside">
			<p>
		<?php } else { ?>	
			<div class="dbx-b-ox-wrapper">
			<fieldset id="bcmax" class="dbx-box">
			<div class="dbx-h-andle-wrapper"><h3 class="dbx-handle"><?php echo BCMAX_NAME;?></h3></div>
			<div class="dbx-c-ontent-wrapper">
			<div id="bcmaxstuff" class="dbx-content">
		<?php } ?>
		<table width="100%" class="editform" bgcolor="#ffffff" cellpadding="2" cellspacing="1">
		<?php if ( $this->bcm_activate == 2 ) { ?>
			<tr bgcolor="#f8f8f8">
			 <td><div align="right">Include this post in BlugRush Feed: </div></td>
			 <td><input type="checkbox" name="bcm_include" value="true" id="bcm_include" <?php echo $bcm_checked;?> class="checkbox" /></td>
			</tr>
			<tr bgcolor="#f0f0f0">
			 <td><div align="right">Custom BlogRush Title: </div></td>
			 <td><input type="text" id="bcm_title" name="bcm_title" value="<?php echo stripslashes($bcm_title);?>" onkeydown="theCounter(document.getElementById('bcm_title'),document.getElementById('maxcounter'));" onkeyup="theCounter(document.getElementById('bcm_title'),document.getElementById('maxcounter'));" maxlength="40" size="40" />
			 &nbsp;<span id='maxcounter' style="background-color:#FFFFFF"><?php echo $bcm_title_len;?></span></td>
			</tr>
			<tr bgcolor="#f8f8f8">
			 <td><div align="right">Link for custom BlogRush feed: </div></td>
			 <td><input type="text" name="bcm_feed_url" value="<?php echo $bcm_feed_url;?>" size="44" onclick="this.select();" readonly><br />
			 (Use this Feed URL for Blogrush)</td>
			</tr>
			<?php 
			if ( !isset($_GET['dnl']) ) {	
				$bcm_version_chk = $this->bcmRecheckData();
				if ( ($bcm_version_chk == '') || strtotime(date('Y-m-d H:i:s')) > (strtotime($bcm_version_chk['last_checked_on']) + $bcm_version_chk['recheck_interval']*60*60) ) {
					$update_arr = $this->bcmExtractUpdateData();
					if ( count($update_arr) > 0 ) {
						$latest_version   = $update_arr[0];
						$recheck_interval = $update_arr[1];
						$download_url     = $update_arr[2];
						$msg_in_plugin    = $update_arr[3];
						$msg_in_plugin    = $update_arr[4];
						$upgrade_url      = $update_arr[5];
						if( BCMAX_VERSION < $latest_version ) {
							$bcm_version_check = array('recheck_interval' => $recheck_interval, 'last_checked_on' => date('Y-m-d H:i:s'));
							$this->bcmRecheckData($bcm_version_check);
							$msg_in_plugin = str_replace("%latest-version%", $latest_version, $msg_in_plugin);
							$msg_in_plugin = str_replace("%plugin-name%", BCMAX_NAME, $msg_in_plugin);
							$msg_in_plugin = str_replace("%upgrade-url%", $upgrade_url, $msg_in_plugin);
							$msg_in_plugin = '<div style="border-bottom:1px solid #CCCCCC;background-color:#FFFEEB;padding:6px;font-size:11px;text-align:center">'.$msg_in_plugin.'</div>';
						} else {
							$msg_in_plugin = '';
						}
					}
				}
			}
			if ( trim($msg_in_plugin) != '' && !isset($_GET['dnl']) ) { ?>
				<tr bgcolor="#f0f0f0">
				 <td colspan="2"><div align="center"><?php echo $msg_in_plugin; ?></div></td>
				</tr>
				<?php 
			}
		} else { ?>
			<tr bgcolor="#f8f8f8">
			 <td colspan="2"><div align="center">Please register the plugin to activate it (Registration is free) <br /><a href="<?php bloginfo('siteurl');?>/wp-admin/options-general.php?page=<?php echo $this->bcm_path;?>" target="_blank">Click here to register</a> </div></td>
			</tr>
        <?php } ?>
		<tr bgcolor="#f0f0f0">
		 <td colspan="2">
		 <table width="100%">
		  <tr>
		   <td><?php echo $this->img_how;?> <a href="http://www.maxblogpress.com/plugins/bcm/bcm-use/" target="_blank">How to use it?</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  
		   <?php echo $this->img_comment;?> <a href="http://www.maxblogpress.com/plugins/bcm/bcm-comments/" target="_blank">Comments and Suggestions</a></td>
		   <td><div align="right">By <a href="http://www.maxblogpress.com" target="_blank">MaxBlogPress</a></div></td>
		  </tr>
		 </table>
		 </td>
		</tr>
		</table>
		<?php if ( $wp_version >= 2.5 ) { ?>
		</p></div></div>	
		<?php } else { ?>	
		</div></div></fieldset></div>
		<?php } 
	}
	
	/**
	 * Adds/edits/deletes Blogrush Click Maximizer's Meta data
	 * @param integer $id
	 * @access public
	 */
	function bcmEditMetaData($id) {
		global $wpdb;
		if ( !isset($id) ) {
			$id = $_REQUEST['post_ID'];
		}
		if( !current_user_can('edit_post', $id) ) {
			return $id;
		}	
		$bcm_include = stripslashes(trim($_REQUEST['bcm_include']));
		$bcm_title   = stripslashes(trim($_REQUEST['bcm_title']));
		delete_post_meta($id, 'bcm_include');
		delete_post_meta($id, 'bcm_title');
		if( $bcm_include == 'true' ) {
			add_post_meta( $id, 'bcm_include', 'true' );
		} else {
			add_post_meta( $id, 'bcm_include', 'false' );
		}
		if( $bcm_title ) {
			add_post_meta( $id, 'bcm_title', $bcm_title );
		}
	}
	
	/**
	 * Gets recheck data fro displaying auto upgrade information
	 */
	function bcmRecheckData($data='') {
		if ( $data != '' ) {
			update_option('bcm_version_check',$data);
		} else {
			$version_chk = get_option('bcm_version_check');
			return $version_chk;
		}
	}
	
	/**
	 * Extracts plugin update data
	 */
	function bcmExtractUpdateData() {
		$arr = array();
		$version_chk_file = "http://www.maxblogpress.com/plugin-updates/blogrush-click-maximizer.php?v=".BCMAX_VERSION;
		$content = wp_remote_fopen($version_chk_file);
		if ( $content ) {
			$content          = nl2br($content);
			$content_arr      = explode('<br />', $content);
			$latest_version   = trim(trim(strstr($content_arr[0],'~'),'~'));
			$recheck_interval = trim(trim(strstr($content_arr[1],'~'),'~'));
			$download_url     = trim(trim(strstr($content_arr[2],'~'),'~'));
			$msg_plugin_mgmt  = trim(trim(strstr($content_arr[3],'~'),'~'));
			$msg_in_plugin    = trim(trim(strstr($content_arr[4],'~'),'~'));
			$upgrade_url      = $this->bcm_siteurl.'/wp-admin/options-general.php?page='.$this->bcm_path.'&action=upgrade&dnl='.$download_url;
			$arr = array($latest_version, $recheck_interval, $download_url, $msg_plugin_mgmt, $msg_in_plugin, $upgrade_url);
		}
		return $arr;
	}
	
	/**
	 * Interface for upgrading plugin
	 */
	function bcmUpgradePlugin() {
		global $wp_version;
		$plugin = $this->bcm_path;
		
		echo '<div class="wrap">';
		echo '<h2>'.BCMAX_NAME.' '.BCMAX_VERSION.'</h2>';
		echo '<h3>Upgrade Plugin &raquo;</h3>';
		if ( $wp_version >= 2.5 ) {
			$res = $this->bcmDoPluginUpgrade($plugin);
		} else {
			echo '&raquo; Wordpress 2.5 or higher required for automatic upgrade.<br><br>';
		}
		if ( $res == false ) echo '&raquo; Plugin couldn\'t be upgraded.<br><br>';
		echo '<br><strong><a href="'.$this->bcm_siteurl.'/wp-admin/plugins.php">Go back to plugins page</a> | <a href="'.$this->bcm_siteurl.'/wp-admin/options-general.php?page='.$this->bcm_path.'">'.BCMAX_NAME.' home page</a></strong>';
		echo '<p style="text-align:center;margin-top:3em;"><strong>'.BCMAX_NAME.' '.BCMAX_VERSION.' by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>';
		echo '</div>';
		include('admin-footer.php');
	}
	
	/**
	 * Carries out plugin upgrade
	 */
	function bcmDoPluginUpgrade($plugin) {
		set_time_limit(300);
		global $wp_filesystem;
		$debug = 0;
		$was_activated = is_plugin_active($plugin); // Check current status of the plugin to retain the same after the upgrade

		// Is a filesystem accessor setup?
		if ( ! $wp_filesystem || !is_object($wp_filesystem) ) {
			WP_Filesystem();
		}
		if ( ! is_object($wp_filesystem) ) {
			echo '&raquo; Could not access filesystem.<br /><br />';
			return false;
		}
		if ( $wp_filesystem->errors->get_error_code() ) {
			echo '&raquo; Filesystem error '.$wp_filesystem->errors.'<br /><br />';
			return false;
		}
		
		if ( $debug ) echo '> File System Okay.<br /><br />';
		
		// Get the URL to the zip file
		$package = $_GET['dnl'];
		if ( empty($package) ) {
			echo '&raquo; Upgrade package not available.<br /><br />';
			return false;
		}
		// Download the package
		$file = download_url($package);
		if ( is_wp_error($file) || $file == '' ) {
			echo '&raquo; Download failed. '.$file->get_error_message().'<br /><br />';
			return false;
		}
		$working_dir = $this->bcm_abspath . 'wp-content/upgrade/' . basename($plugin, '.php');
		
		if ( $debug ) echo '> Working Directory = '.$working_dir.'<br /><br />';
		
		// Unzip package to working directory
		$result = $this->bcmUnzipFile($file, $working_dir);
		if ( is_wp_error($result) ) {
			unlink($file);
			$wp_filesystem->delete($working_dir, true);
			echo '&raquo; Couldn\'t unzip package to working directory. Make sure that "/wp-content/upgrade/" folder has write permission (CHMOD 755).<br /><br />';
			return $result;
		}
		
		if ( $debug ) echo '> Unzip package to working directory successful<br /><br />';
		
		// Once extracted, delete the package
		unlink($file);
		if ( is_plugin_active($plugin) ) {
			deactivate_plugins($plugin, true); //Deactivate the plugin silently, Prevent deactivation hooks from running.
		}
		
		// Remove the old version of the plugin
		$plugin_dir = dirname($this->bcm_abspath . PLUGINDIR . "/$plugin");
		$plugin_dir = trailingslashit($plugin_dir);
		// If plugin is in its own directory, recursively delete the directory.
		if ( strpos($plugin, '/') && $plugin_dir != $base . PLUGINDIR . '/' ) {
			$deleted = $wp_filesystem->delete($plugin_dir, true);
		} else {

			$deleted = $wp_filesystem->delete($base . PLUGINDIR . "/$plugin");
		}
		if ( !$deleted ) {
			$wp_filesystem->delete($working_dir, true);
			echo '&raquo; Could not remove the old plugin. Make sure that "/wp-content/plugins/" folder has write permission (CHMOD 755).<br /><br />';
			return false;
		}
		
		if ( $debug ) echo '> Old version of the plugin removed successfully.<br /><br />';

		// Copy new version of plugin into place
		if ( !$this->bcmCopyDir($working_dir, $this->bcm_abspath . PLUGINDIR) ) {
			echo '&raquo; Installation failed. Make sure that "/wp-content/plugins/" folder has write permission (CHMOD 755)<br /><br />';
			return false;
		}
		//Get a list of the directories in the working directory before we delete it, we need to know the new folder for the plugin
		$filelist = array_keys( $wp_filesystem->dirlist($working_dir) );
		// Remove working directory
		$wp_filesystem->delete($working_dir, true);
		// if there is no files in the working dir
		if( empty($filelist) ) {
			echo '&raquo; Installation failed.<br /><br />';
			return false; 
		}
		$folder = $filelist[0];
		$plugin = get_plugins('/' . $folder);      // Pass it with a leading slash, search out the plugins in the folder, 
		$pluginfiles = array_keys($plugin);        // Assume the requested plugin is the first in the list
		$result = $folder . '/' . $pluginfiles[0]; // without a leading slash as WP requires
		
		if ( $debug ) echo '> Copy new version of plugin into place successfully.<br /><br />';
		
		if ( is_wp_error($result) ) {
			echo '&raquo; '.$result.'<br><br>';
			return false;
		} else {
			//Result is the new plugin file relative to PLUGINDIR
			echo '&raquo; Plugin upgraded successfully<br><br>';	
			if( $result && $was_activated ){
				echo '&raquo; Attempting reactivation of the plugin...<br><br>';	
				echo '<iframe style="display:none" src="' . wp_nonce_url('update.php?action=activate-plugin&plugin=' . $result, 'activate-plugin_' . $result) .'"></iframe>';
				sleep(15);
				echo '&raquo; Plugin reactivated successfully.<br><br>';	
			}
			return true;
		}
	}
	
	/**
	 * Copies directory from given source to destinaktion
	 */
	function bcmCopyDir($from, $to) {
		global $wp_filesystem;
		$dirlist = $wp_filesystem->dirlist($from);
		$from = trailingslashit($from);
		$to = trailingslashit($to);
		foreach ( (array) $dirlist as $filename => $fileinfo ) {
			if ( 'f' == $fileinfo['type'] ) {
				if ( ! $wp_filesystem->copy($from . $filename, $to . $filename, true) ) return false;
				$wp_filesystem->chmod($to . $filename, 0644);
			} elseif ( 'd' == $fileinfo['type'] ) {
				if ( !$wp_filesystem->mkdir($to . $filename, 0755) ) return false;
				if ( !$this->bcmCopyDir($from . $filename, $to . $filename) ) return false;
			}
		}
		return true;
	}
	
	/**
	 * Unzips the file to given directory
	 */
	function bcmUnzipFile($file, $to) {
		global $wp_filesystem;
		if ( ! $wp_filesystem || !is_object($wp_filesystem) )
			return new WP_Error('fs_unavailable', __('Could not access filesystem.'));
		$fs =& $wp_filesystem;
		require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
		$archive = new PclZip($file);
		// Is the archive valid?
		if ( false == ($archive_files = $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING)) )
			return new WP_Error('incompatible_archive', __('Incompatible archive'), $archive->errorInfo(true));
		if ( 0 == count($archive_files) )
			return new WP_Error('empty_archive', __('Empty archive'));
		$to = trailingslashit($to);
		$path = explode('/', $to);
		$tmppath = '';
		for ( $j = 0; $j < count($path) - 1; $j++ ) {
			$tmppath .= $path[$j] . '/';
			if ( ! $fs->is_dir($tmppath) )
				$fs->mkdir($tmppath, 0755);
		}
		foreach ($archive_files as $file) {
			$path = explode('/', $file['filename']);
			$tmppath = '';
			// Loop through each of the items and check that the folder exists.
			for ( $j = 0; $j < count($path) - 1; $j++ ) {
				$tmppath .= $path[$j] . '/';
				if ( ! $fs->is_dir($to . $tmppath) )
					if ( !$fs->mkdir($to . $tmppath, 0755) )
						return new WP_Error('mkdir_failed', __('Could not create directory'));
			}
			// We've made sure the folders are there, so let's extract the file now:
			if ( ! $file['folder'] )
				if ( !$fs->put_contents( $to . $file['filename'], $file['content']) )
					return new WP_Error('copy_failed', __('Could not copy file'));
				$fs->chmod($to . $file['filename'], 0755);
		}
		return true;
	}
	
	/**
	 * Plugin registration form
	 * @access public 
	 */
	function bcmRegistrationForm($form_name, $submit_btn_txt='Register', $name, $email, $hide=0, $submit_again='') {
		$wp_url = get_bloginfo('wpurl');
		$wp_url = (strpos($wp_url,'http://') === false) ? get_bloginfo('siteurl') : $wp_url;
		$thankyou_url = $wp_url.'/wp-admin/options-general.php?page='.$_GET['page'];
		$onlist_url   = $wp_url.'/wp-admin/options-general.php?page='.$_GET['page'].'&amp;mbp_onlist=1';
		if ( $hide == 1 ) $align_tbl = 'left';
		else $align_tbl = 'center';
		?>
		
		<?php if ( $submit_again != 1 ) { ?>
		<script><!--
		function trim(str){
			var n = str;
			while ( n.length>0 && n.charAt(0)==' ' ) 
				n = n.substring(1,n.length);
			while( n.length>0 && n.charAt(n.length-1)==' ' )	
				n = n.substring(0,n.length-1);
			return n;
		}
		function bcmValidateForm_0() {
			var name = document.<?php echo $form_name;?>.name;
			var email = document.<?php echo $form_name;?>.from;
			var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
			var err = ''
			if ( trim(name.value) == '' )
				err += '- Name Required\n';
			if ( reg.test(email.value) == false )
				err += '- Valid Email Required\n';
			if ( err != '' ) {
				alert(err);
				return false;
			}
			return true;
		}
		//-->
		</script>
		<?php } ?>
		<table align="<?php echo $align_tbl;?>">
		<form name="<?php echo $form_name;?>" method="post" action="http://www.aweber.com/scripts/addlead.pl" <?php if($submit_again!=1){;?>onsubmit="return bcmValidateForm_0()"<?php }?>>
		 <input type="hidden" name="unit" value="maxbp-activate">
		 <input type="hidden" name="redirect" value="<?php echo $thankyou_url;?>">
		 <input type="hidden" name="meta_redirect_onlist" value="<?php echo $onlist_url;?>">
		 <input type="hidden" name="meta_adtracking" value="bcm-w-activate">
		 <input type="hidden" name="meta_message" value="1">
		 <input type="hidden" name="meta_required" value="from,name">
	 	 <input type="hidden" name="meta_forward_vars" value="1">	
		 <?php if ( $submit_again == 1 ) { ?> 	
		 <input type="hidden" name="submit_again" value="1">
		 <?php } ?>		 
		 <?php if ( $hide == 1 ) { ?> 
		 <input type="hidden" name="name" value="<?php echo $name;?>">
		 <input type="hidden" name="from" value="<?php echo $email;?>">
		 <?php } else { ?>
		 <tr><td>Name: </td><td><input type="text" name="name" value="<?php echo $name;?>" size="25" maxlength="150" /></td></tr>
		 <tr><td>Email: </td><td><input type="text" name="from" value="<?php echo $email;?>" size="25" maxlength="150" /></td></tr>
		 <?php } ?>
		 <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="<?php echo $submit_btn_txt;?>" class="button" /></td></tr>
		 </form>
		</table>
		<?php
	}
	
	/**
	 * Register Plugin - Step 2
	 * @access public 
	 */
	function bcmRegister_2($form_name='frm2',$name,$email) {
		$msg = 'You have not clicked on the confirmation link yet. A confirmation email has been sent to you again. Please check your email and click on the confirmation link to activate the plugin.';
		if ( trim($_GET['submit_again']) != '' && $msg != '' ) {
			echo '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>';
		}
		?>
		<div class="wrap"><h2> <?php echo BCMAX_NAME.' '.BCMAX_VERSION; ?></h2>
		 <center>
		 <table width="640" cellpadding="5" cellspacing="1" bgcolor="#ffffff" style="border:1px solid #e9e9e9">
		  <tr><td align="center"><h3>Almost Done....</h3></td></tr>
		  <tr><td><h3>Step 1:</h3></td></tr>
		  <tr><td>A confirmation email has been sent to your email "<?php echo $email;?>". You must click on the link inside the email to activate the plugin.</td></tr>
		  <tr><td><strong>The confirmation email will look like:</strong><br /><img src="http://www.maxblogpress.com/images/activate-plugin-email.jpg" vspace="4" border="0" /></td></tr>
		  <tr><td>&nbsp;</td></tr>
		  <tr><td><h3>Step 2:</h3></td></tr>
		  <tr><td>Click on the button below to Verify and Activate the plugin.</td></tr>
		  <tr><td><?php $this->bcmRegistrationForm($form_name.'_0','Verify and Activate',$name,$email,$hide=1,$submit_again=1);?></td></tr>
		 </table>
		 <p>&nbsp;</p>
		 <table width="640" cellpadding="5" cellspacing="1" bgcolor="#ffffff" style="border:1px solid #e9e9e9">
           <tr><td><h3>Troubleshooting</h3></td></tr>
           <tr><td><strong>The confirmation email is not there in my inbox!</strong></td></tr>
           <tr><td>Dont panic! CHECK THE JUNK, spam or bulk folder of your email.</td></tr>
           <tr><td>&nbsp;</td></tr>
           <tr><td><strong>It's not there in the junk folder either.</strong></td></tr>
           <tr><td>Sometimes the confirmation email takes time to arrive. Please be patient. WAIT FOR 6 HOURS AT MOST. The confirmation email should be there by then.</td></tr>
           <tr><td>&nbsp;</td></tr>
           <tr><td><strong>6 hours and yet no sign of a confirmation email!</strong></td></tr>
           <tr><td>Please register again from below:</td></tr>
           <tr><td><?php $this->bcmRegistrationForm($form_name,'Register Again',$name,$email,$hide=0,$submit_again=2);?></td></tr>
           <tr><td><strong>Help! Still no confirmation email and I have already registered twice</strong></td></tr>
           <tr><td>Okay, please register again from the form above using a DIFFERENT EMAIL ADDRESS this time.</td></tr>
           <tr><td>&nbsp;</td></tr>
           <tr>
             <td><strong>Why am I receiving an error similar to the one shown below?</strong><br />
                 <img src="http://www.maxblogpress.com/images/no-verification-error.jpg" border="0" vspace="8" /><br />
               You get that kind of error when you click on &quot;Verify and Activate&quot; button or try to register again.<br />
               <br />
               This error means that you have already subscribed but have not yet clicked on the link inside confirmation email. In order to  avoid any spam complain we don't send repeated confirmation emails. If you have not recieved the confirmation email then you need to wait for 12 hours at least before requesting another confirmation email. </td>
           </tr>
           <tr><td>&nbsp;</td></tr>
           <tr><td><strong>But I've still got problems.</strong></td></tr>
           <tr><td>Stay calm. <strong><a href="http://www.maxblogpress.com/contact-us/" target="_blank">Contact us</a></strong> about it and we will get to you ASAP.</td></tr>
         </table>
		 </center>		
		<p style="text-align:center;margin-top:3em;"><strong><?php echo BCMAX_NAME.' '.BCMAX_VERSION; ?> by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>
	    </div>
		<?php
	}

	/**
	 * Register Plugin - Step 1
	 * @access public 
	 */
	function bcmRegister_1($form_name='frm1') {
		global $userdata;
		$name  = trim($userdata->first_name.' '.$userdata->last_name);
		$email = trim($userdata->user_email);
		?>
		<div class="wrap"><h2> <?php echo BCMAX_NAME.' '.BCMAX_VERSION; ?></h2>
		 <center>
		 <table width="620" cellpadding="3" cellspacing="1" bgcolor="#ffffff" style="border:1px solid #e9e9e9">
		  <tr><td align="center"><h3>Please register the plugin to activate it. (Registration is free)</h3></td></tr>
		  <tr><td align="left">In addition you'll receive complimentary subscription to MaxBlogPress Newsletter which will give you many tips and tricks to attract lots of visitors to your blog.</td></tr>
		  <tr><td align="center"><strong>Fill the form below to register the plugin:</strong></td></tr>
		  <tr><td><?php $this->bcmRegistrationForm($form_name,'Register',$name,$email);?></td></tr>
		  <tr><td align="center"><font size="1">[ Your contact information will be handled with the strictest confidence <br />and will never be sold or shared with third parties ]</font></td></td></tr>
		 </table>
		 </center>
		<p style="text-align:center;margin-top:3em;"><strong><?php echo BCMAX_NAME.' '.BCMAX_VERSION; ?> by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>
	    </div>
		<?php
	}
	
} // Eof Class

$BCMax = new BCMax();
?>