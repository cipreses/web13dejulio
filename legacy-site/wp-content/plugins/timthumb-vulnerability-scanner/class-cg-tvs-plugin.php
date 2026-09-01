<?php
if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}

class CG_TVS_Plugin{

  var $script_latest_version;
  var $script_safe_version;
  var $last_version_check;
  var $scan_daily;

  var $last_scan;
  var $script_instances;
  var $suspicious_files;

  var $plugin_base;

  var $current_timthumb_src_version;
  var $on_admin_page = false;

  function init(){
    global $pagenow;
    if($pagenow == 'tools.php' && $_GET['page'] == 'cg-timthumb-scanner'){
      $this->on_admin_page = true;
    }
    $storage_array = get_option( 'cg_tvs_data' );
    if(is_array($storage_array)){
      $this->script_latest_version      = $storage_array['script_latest_version'];
      $this->script_safe_version        = $storage_array['script_safe_version'];
      $this->last_version_check         = $storage_array['last_version_check'];

      $this->script_instances           = $storage_array['script_instances'];
      $this->suspicious_files           = $storage_array['suspicious_files'];
      $this->last_scan                  = $storage_array['last_scan'];
      $this->scan_daily                 = $storage_array['scan_daily'];

      $this->scan_summary               = $storage_array['scan_summary'];

    }else{
      $this->script_latest_version      = '2.8.11';
      $this->script_safe_version        = '2.8.2';

      $this->last_version_check         = 0;

      $this->script_instances           = array();
      $this->suspicious_files           = array();
      $this->last_scan                  = 0;
      $this->scan_daily                 = true;
      $this->scan_summary['Vulnerable'] = 0;
      $this->scan_summary['Outdated']   = 0;
      $this->scan_summary['Up to Date'] = 0;

      $this->save();
    }
    $this->plugin_base_dir = trailingslashit(dirname(__FILE__));

    if( $this->scan_summary['Outdated'] > 0 && !$this->on_admin_page ){
      $this->show_message( $this->scan_summary['Outdated']." outdated Timthumb "._n('file', 'files', $this->scan_summary['Outdated'])." found.  <a href=\"tools.php?page=cg-timthumb-scanner\">Fix "._n('it', 'them', $this->scan_summary['Outdated'])." here</a>.", $error = false );
    }
    if( $this->scan_summary['Vulnerable'] > 0 && !$this->on_admin_page ){
      $this->show_message( $this->scan_summary['Vulnerable']." vulnerable Timthumb "._n('file', 'files', $this->scan_summary['Vulnerable'])." found.  <a href=\"tools.php?page=cg-timthumb-scanner\">Fix "._n('it', 'them', $this->scan_summary['Vulnerable'])." here</a>.", $error = true );
    }
  }

  function add_menus(){
  	add_management_page( 'Timthumb Scanner', 'Timthumb Scanner', 'manage_options', 'cg-timthumb-scanner', array(&$this, 'admin_panel_controller' ) );
  }


  function activate(){
    $this->init();
    // Clear out older version data
  	delete_option( 'cg_tvs_last_checked' );
  	delete_option( 'cg_tvs_vulnerable_files' );
  	delete_option( 'cg_tvs_safe_files' );
  }

  function deactivate(){
  	wp_clear_scheduled_hook('cg_tvs_daily_scan');
  	delete_option( 'cg_tvs_data' );
  }

  function scan( $scan_base = WP_CONTENT_DIR ){
    // We need to make sure we're using uniform directory separators
    // to match filenames
    $scan_base = str_replace('/', DIRECTORY_SEPARATOR, $scan_base);
  	require_once 'class-cg-tvs-filescanner.php';
  	$scanner = new CG_FileScanner( $scan_base );
  	$scanner->generate_inventory();
  	$scanner->scan_inventory_timthumb();
  	$scanner->check_for_intrusion_files();
    $this->last_scan = time();
    $this->script_instances = $scanner->instances;
    $this->suspicious_files = $scanner->suspicious_files;
    $this->scan_summary['Vulnerable'] = 0;
    $this->scan_summary['Outdated']   = 0;
    $this->scan_summary['Up to Date'] = 0;
    if(is_array($scanner->instances)){
      foreach($scanner->instances as $file){
        $this->scan_summary[$this->get_version_status($file['version'])]++;
      }
    }
  	$this->show_message('Scan completed.');
  	$this->save();
  }

/*
  function get_version_float($version){
    // Convert version string into a float
    $version_parts = explode('.', $version);
    $version = intval($version_parts[0]).'.';
    for( $position = 1; $position<count($version_parts); ++$position ){
      $version .= intval($version_parts[$position]);
    }

    return floatval($version);

  }
*/

  function get_version_status($version){

    if( version_compare( $version, $this->script_safe_version, '<' ) ){
      return "Vulnerable";
    }
    if( version_compare( $version, $this->script_latest_version, '<' ) ){
      return "Outdated";
    }else{
      return "Up to Date";
    }
  }
  function display_version_status($version){
    $status = $this->get_version_status($version);
    switch($status){
      case 'Vulnerable':
        return "<span style='color:red'>Vulnerable</span>";
        break;
      case 'Outdated':
        return "<span style='color:#FFCC00'>Outdated</span>";
        break;
      case 'Up to Date':
        return "<span style='color:forestgreen'>Up to Date</span>";
        break;
    }
  }
  function fix_file( $file, $backup = 'without-backup' ) {
    if($backup == 'with-backup'){
      $this->backup_file( $file );
    }

    if(FALSE === $latest_src = $this->get_timthumb_src()){
      $this->show_message('We can\'t read updated timthumb source file, so we can\'t update the selected files.  Try checking permissions on the plugin folder and the file "cg-tvs-timthumb-latest.txt", if it exists.');
    }


  	if( FALSE !== $fw = @fopen( $file, 'w' ) ) {
  		if ( fwrite( $fw, $latest_src ) ) {
  			$this->show_message( 'File <strong>' . basename( $file ) . '</strong> at <em>' . $file . '</em> successfully upgraded.' );
  		} else {
  			$this->show_message( 'Unknown file write error.', true );
  		}
  	} else {
  		$this->show_message( 'CAN\'T OPEN VULNERABLE FILE FOR WRITING', true );
  		return;
  	}
  }

  function get_timthumb_src_version(){
    $current_src = $this->get_timthumb_src();
    preg_match( "~define\s*\(\s*[\'|\"]VERSION[\'|\"],\s*[\'|\"]([^\'|\"]*)~", $current_src, $matches );
    $this->current_timthumb_src_version = $matches[1];
  }

  function download_new_timthumb_src(){
    $tmp_filename = download_url( 'http://timthumb.googlecode.com/svn/trunk/timthumb.php' );
		if ( is_wp_error( $tmp_filename ) ) {
      $this->show_message( 'Error downloading updated copy of timthumb.php.  Can\'t fix outdated files.' );
      return false;
		}
    @unlink($this->plugin_base_dir . 'cg-tvs-timthumb-latest.txt');
    rename($tmp_filename, $this->plugin_base_dir . 'cg-tvs-timthumb-latest.txt');
    @unlink($tmp_filename);
    if(is_file($this->plugin_base_dir . 'cg-tvs-timthumb-latest.txt')){
      $this->get_timthumb_src_version();
      $this->show_message( 'Updated copy of timthumb downloaded successfully.' );
      return true;
    }else{
      $this->show_message( 'Error downloading updated copy of timthumb.php.  Can\'t fix outdated files.' );
      return false;
    }
  }

  function get_timthumb_src(){
  	$src_file_path = $this->plugin_base_dir . 'cg-tvs-timthumb-latest.txt';
  	if ( FALSE !== $fr = @fopen( $src_file_path, 'r' ) ) {
  		$latest_src = fread( $fr, filesize( $src_file_path ) );
  		fclose($fr);
  	} else {
  		return false;
  	}
  	return $latest_src;
  }

  function backup_file( $path ){
    $backup_path = $this->plugin_base_dir . '/backups' . $path;
    wp_mkdir_p( dirname( $backup_path ) );
    copy($path, $backup_path);
  }

  function save(){
    $storage_array['script_latest_version']      = $this->script_latest_version;
    $storage_array['script_safe_version']        = $this->script_safe_version;
    $storage_array['last_version_check']         = $this->last_version_check;

    $storage_array['script_instances']           = $this->script_instances;
    $storage_array['suspicious_files']           = $this->suspicious_files;
    $storage_array['last_scan']                  = $this->last_scan;
    $storage_array['scan_daily']                 = $this->scan_daily;

    $storage_array['scan_summary']               = $this->scan_summary;
    update_option( 'cg_tvs_data', $storage_array );

  }

  function show_message( $message, $error = false )
  {

    if(!is_admin() || DOING_CRON === TRUE){
      return;
    }
  	if ($error) {
  		echo '<div id="message" class="error">';
  	}
  	else {
  		echo '<div id="message" class="updated fade">';
  	}

  	echo "<p><strong>$message</strong></p></div>";
  }

  function admin_panel_controller(){
  	if ( ! current_user_can( 'manage_options' ) )  {
  		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  	}

    if(!is_writeable($this->plugin_base_dir)){
      $this->show_message('The plugin directory (at '.$this->plugin_base_dir.') is not writeable.  Because of this, we can\'t download an updated copy of timthumb to use.  Try changing permissions on this directory to 755 (or in certain cases, 777)', 'error');
    }

  	if ( isset( $_REQUEST['cg-tvs-action'] ) ) {
  		switch ( $_REQUEST['cg-tvs-action'] ) {
  			case 'scan':
  				$this->scan();
  				break;
  			case 'fix':
  			  $this->get_timthumb_src_version();
  			  if(version_compare($this->current_timthumb_src_version, $this->script_latest_version, '<')){
  			   $this->download_new_timthumb_src();
  			  }
  				if ( wp_verify_nonce( $_POST['_wpnonce'], 'fix_timthumb_files' ) ) {
  				  if( is_array( $_POST['fix'] ) && !empty( $_POST['fix'] ) ){
  				    foreach( $_POST['fix'] as $file_id ) {
        				$this->fix_file( $this->script_instances[intval($file_id)]['path'] );
  				    }
  				  }
    				$this->scan(); // Re-scan site
  				}
  				break;
  			case 'fixall':
  				$nonce = $_GET['_wpnonce'];
  				if ( wp_verify_nonce( $nonce, 'fix_all_timthumb_files' ) ) {
  					$vulnerable_files = $this->get_vulnerable_files();
  					if ( is_array( $vulnerable_files ) && ! empty( $vulnerable_files ) ) {
  						foreach ( $vulnerable_files as $file ) {
  							$this->fix_file( $file );
  						}
  						$this->scan(); // Re-scan site
  					}
  				}
  				break;
  		  case 'update-options':
  				$nonce = $_POST['_wpnonce'];
  				if ( wp_verify_nonce( $nonce, 'update_tvs_options' ) ) {
  				  if($_POST['scan-daily']){
  				    $this->scan_daily = true;
            	if ( !wp_next_scheduled( 'cg_tvs_daily_scan' ) ) {
            		wp_schedule_event(time(), 'daily', 'cg_tvs_daily_scan');
            	}
  				  }else{
  				    $this->scan_daily = false;
            	wp_clear_scheduled_hook('cg_tvs_daily_scan');
  				  }
  				}
  		    break;
  		}
  	}

      if(!empty($this->suspicious_files)){
        $this->show_message('<strong>Oh no!</strong> Files on your site indicate that your server has already been compromised by the timthumb vulnerability.', 'error');
      }

  	include_once 'cg-tvs-admin-panel-display.php';
  }

}
