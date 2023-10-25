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

  $today= date("Y-m-d");
  $today = date_create(date('Y-m-d'));
  $yesterday = date_sub($today, date_interval_create_from_date_string("1 days"))->format("Y-m-d");
  $format= "json";
  $filename= "import-KTB-".$yesterday.".".$format;

  $api= "api/v1/data/";
  $form= 453213;
  //$query= '?query={"today":"'.$yesterday.'"}';
  $query= '?query={"_submission_time":{"$gt":"'.$yesterday.'"}}';

  $opts = array(
    'http'=>array(
      'method'=>"GET",
      'header'=>"Content-Type: application/octet-stream\r\n".
                "Authorization: Token ".$token."\r\n" 
    )
  );

  $context = stream_context_create($opts);

  if ($verbose) print('Downloading '.$server_url.$api.$form.$query." ...\n");

  $json = file_get_contents($server_url.$api.$form.$query, false, $context);
/*  $size = file_put_contents($filename, $json);
  if ($size == 0) { 
    error_log('Empty file !');
    exit(2);
  }*/

  $records = json_decode($json, true);
  foreach ($records as $contact) {
//  print_r($record);
    print("- ".$contact['contact/lastname'].", ".$contact['contact/firstname']."\nDate : ".$contact['end']."\nPhoto : ");
    foreach ($contact['_attachments'] as $attachment) {
      print($attachment['filename']."\n");
    }
    print("\n");
  }

//  if ($verbose) print("... done. File size : " . $size . " b\n");
?>
