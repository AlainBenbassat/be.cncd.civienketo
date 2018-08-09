<?php
use CRM_Civienketo_ExtensionUtil as E;

class CRM_Civienketo_Page_EnketoImport extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(E::ts('Import form records'));
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
    if (!isset($_REQUEST['form_id'])) {
      CRM_Core_Session::setStatus(ts('You must specify a form.'), ts('Import Error'), 'error');
      parent::run();
      return;
    } else {
      $form = $_REQUEST['form_id'];
      // TODO Test if this form exist in CiviEnketo
    }
    

    // --- Download file ---
    $today = date_create(date('Y-m-d'));
    $yesterday = date_sub($today, date_interval_create_from_date_string("1 days"))->format("Y-m-d");
    $uploadDir = CRM_Core_Config::singleton()->uploadDir;
    $filename= $uploadDir.'enketo_data-'.$form.'-'.$yesterday.'.json';

    $api= "api/v1/data/";
    $query= '?query={"_submission_time":{"$gt":"'.$yesterday.'"}}';

    $opts = array(
      'http'=>array(
        'method'=>"GET",
        'header'=>"Content-Type: application/octet-stream\r\n".
                  "Authorization: Token ".$token."\r\n" 
      )
    );
    $context = stream_context_create($opts);

    $logs_download[] = ts('- Downloading form n.').' '.$form.'... ';
 
    $data = file_get_contents($server_url.$api.$form.$query, false, $context);
    $size = file_put_contents($filename, $data);
    if ($size == 0) { 
      CRM_Core_Session::setStatus(ts('Downladed file is empty.'), ts('Import waring'), 'warn');
    }
  
    if ($verbose) {
      $logs_download[] = '- '.ts('Server url: ').$server_url.$api.$form.$query;
      $logs_download[] = '- '.ts('File downloaded: ').$filename;
      $logs_download[] = '- '.ts('File size: ').$size." b";
    }

    // Import file 
    $records = json_decode($data, true);
    $importer = new CRM_Civienketo_Importer_CNCD($records);
    $importer->run();
    $logs_import = $importer->getLog();


    // Notify manager by email
    $logs_summary = $importer->getSummary();
    send_mail2contact($manager, "[CiviEnketo] Résultat d'importation",
      "<h1>Rapport d'importation des mandats</h1>".
      "<h2>".ts('Summary')."</h2>".
      $logs_summary);
      
    // Log timestamp in CiviCRM DB
    $result = civicrm_api3('EnketoForm', 'get', [
        'sequential' => 1,
        'form_id' => $form,
      ]);
    $form_id= $result['id'];
  
    $result = civicrm_api3('EnketoForm', 'create', [
        'last_importation_time' => date("Y-m-d H:i:s"),
        'id' => $form_id,
    ]);

    $this->assign('logs_download', $logs_download);
    $this->assign('logs_import', $logs_import);
    $this->assign('logs_summary', $logs_summary);

    parent::run();
  }
}
