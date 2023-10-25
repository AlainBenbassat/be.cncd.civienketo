<?php
/* ***
  Import form from Kobotoolbox
*/
  // --- Bootstrap ---
  require_once '/var/www/sites/all/modules/civicrm/civicrm.config.php';
  require_once 'CRM/Core/Config.php';
  $config = CRM_Core_Config::singleton();
  require_once '/var/www/sites/all/modules/civicrm/api/class.api.php';

  // --- Parameters ---
  $verbose = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_verbose');
  if (!isset($verbose)) {
    $verbose = true;
  } 
  $server_url = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_server_url');
  if (!isset($server_url)) {
    error_log('No server_url settings in CiviCRM');
    exit(1);
  }
  $token = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_server_token');
  if (!isset($server_url)) {
    error_log('No server token settings in CiviCRM');
    exit(1);
  }

  $opts = array(
    'http'=>array(
      'method'=>"GET",
      'header'=>"Content-Type: image/png\r\n".
                "Authorization: Token ".$token."\r\n" 
    )
  );

  $context = stream_context_create($opts);
  $upload_dir = ""; // CRM_Core_Config::singleton()->uploadDir;
  $user= "philippes";
  $formhub= "ea90e9e73e734a65a4d948b375289c72";
  $uuid= "7b87b264-9880-4d2e-ae44-654f694c1933";
  $photo= "profil-11_10_26.png";
  $filename= $user."%2Fattachments%2F".$formhub."%2F".$uuid."%2F".$photo;
  $query= "/media/original?media_file=";

  if ($verbose) print('Downloading '.$filename." ...\n");

  $size = file_put_contents($upload_dir.$filename, file_get_contents($server_url.$query.$filename, false, $context));
  if ($size == 0) { 
    error_log('Empty file !');
    exit(2);
  }

  if ($verbose) print("... done. File size : " . $size . " b\n");
?>
