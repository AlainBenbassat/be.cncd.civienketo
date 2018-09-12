<?php
use CRM_Civienketo_ExtensionUtil as E;

class CRM_Civienketo_Page_EnketoImport extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(E::ts('Import form records'));

    // --- Parameters ---
    $managers_group = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_managers');
    if ((!isset($managers_group) || $managers_group==0)) $managers_group = 1;
    
    $campaign = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_campaign');

    if (!isset($_REQUEST['form_id'])) {
      CRM_Core_Session::setStatus(ts('You must specify a form.'), ts('Import Error'), 'error');
      parent::run();
      return;
    } else {
      $form_id = $_REQUEST['form_id'];
    }
    if (isset($_REQUEST['delay'])) {
      $delay = $_REQUEST['delay'];
    } 
    else
      $delay = 1;

    // Test if this form exist in CiviEnketo
    $result = civicrm_api3('EnketoForm', 'getcount', [
      'form_id' => $form_id,
    ]);
    if ($result==0) {
      CRM_Core_Session::setStatus(ts('Invalid form ID.'), ts('Import Error'), 'error');
      parent::run();
      return;
    }
    
    // Download file 
    $downloader = new CRM_Civienketo_Downloader_KPI();
    $records = $downloader->run($form_id,$delay);
    $logs_download = $downloader->getLog();

    // Import file 
    $importer = new CRM_Civienketo_Importer_CNCD($records);
    $importer->run();
    $logs_import = $importer->getLog();

    // Notify manager by email
    $logs_summary = $importer->getSummary();
    send_mail2group($managers_group, "admcrm@cncd.be", 
                    "[CiviEnketo] Résultat d'importation",
      "<h1>Rapport d'importation des mandats</h1>".
      "<h2>".ts('Details')."</h2>".
      implode($logs_import, '<br>').
      "<h2>".ts('Summary')."</h2>".
      $logs_summary);

    // Log timestamp in CiviCRM DB
    $result = civicrm_api3('EnketoForm', 'get', [
        'sequential' => 1,
        'form_id' => $form_id,
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
