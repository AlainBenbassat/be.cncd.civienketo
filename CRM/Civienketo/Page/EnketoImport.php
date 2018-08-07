<?php
use CRM_Civienketo_ExtensionUtil as E;

class CRM_Civienketo_Page_EnketoImport extends CRM_Core_Page {

  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(E::ts('Import records'));
    $this->assign('currentTime', date('Y-m-d H:i:s'));

    // --- Parameters ---
    $verbose = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_ver
bose');
    if (!isset($verbose)) {
      $verbose = true;
    } 
    $manager = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_manager');
    if (!isset($manager) || $manager==0 ) {
      CRM_Core_Session::setStatus(ts('No manager defined.'), ts('Import Error'), 'error');
      error_log('No manager settings in CiviCRM');
      parent::run();
      return;
    }
    $server_url = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_server_url');
    if (!isset($server_url)) {
      CRM_Core_Session::setStatus(ts('Server_url not defined'), ts('Import Error'), 'error');
      error_log('No server_url settings in CiviCRM');
      parent::run();
      return;
    }
    $token = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_server_token');
    if (!isset($server_url)) {
      CRM_Core_Session::setStatus(ts('Server token not defined'), ts('Import Error'), 'error');
      error_log('No server token settings in CiviCRM');
      parent::run();
      return;
    }
    $campaign = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_campaign');
    if (!isset($campaign) || $campaign==0 ) {
      CRM_Core_Session::setStatus(ts("Campaign is'nt defined"), ts('Import Error'), 'error');
      error_log('No campaign settings in CiviCRM');
      parent::run();
      return;
    }

    // --- Download file ---
    $today = date_create(date('Y-m-d'));
    $yesterday = date_sub($today, date_interval_create_from_date_string("1 days"))->format("Y-m-d");
    $uploadDir = CRM_Core_Config::singleton()->uploadDir;
    $filename= $uploadDir."import-KTB-$yesterday.json";

    $api= "api/v1/data/";
    $form= 168268;
    $query= '?query={"_submission_time":{"$gt":"'.$yesterday.'"}}';

    $opts = array(
      'http'=>array(
        'method'=>"GET",
        'header'=>"Content-Type: application/octet-stream\r\n".
                  "Authorization: Token ".$token."\r\n" 
      )
    );
    $context = stream_context_create($opts);

    if ($verbose) $logs[] = ts('Downloading').' '.$server_url.$api.$form.$query;
 
    $data = file_get_contents($server_url.$api.$form.$query, false, $context);
    $size = file_put_contents($filename, $data);
    if ($size == 0) { 
      CRM_Core_Session::setStatus(ts('Downladed file is empty.'), ts('Import waring'), 'warning');
    }
  
    if ($verbose) {
      $logs[] = ts("- File downloaded: ").$filename;
      $logs[] = ts("- Downloaded size: ").$size." b";
    }


    // --- Import file ---
    $mandates = json_decode($data, true);
    $contact_name = [];


    foreach ($mandates as $mandate) {
      $nb_line++;
  
      $mandate["contact/lastname"]= ucfirst($mandate["contact/lastname"]);
      $mandate["contact/firstname"]= ucfirst($mandate["contact/firstname"]);
      $iban= $mandate['mandate0/iban_country'].$mandate['mandate_num/iban_checksum'].$mandate['mandate_num/iban_account'];
      $contact= $mandate["contact/lastname"].", ".$mandate["contact/firstname"];
      $contacts[]= $contact;    

      if ($verbose) { 
        $logs[] = $nb_line." - ".$contact." - ";
        if ($mandate["mandate/amount"]!="other") {
          $logs[] = $iban." : ".$mandate['mandate/amount']." €";
        } else {    
          $logs[] = $iban." : ".$mandate['mandate/amount_other']." €";
        }
      }
    }

    if ($verbose) {
      $logs[] = ts("done").'.';
    }

    $this->assign('logs', $logs);

    parent::run();
  }

}
