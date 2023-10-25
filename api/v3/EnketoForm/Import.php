<?php
use CRM_Civienketo_ExtensionUtil as E;

/**
 * EnketoForm.Import API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_enketo_form_Import_spec(&$spec) {
  $spec['form_id']['description'] = 'External ID of the form';
  $spec['form_id']['api.required'] = 1;
  $spec['delay']['description'] = 'Number of day in the past';
}

/**
 * EnketoForm.Import API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_enketo_form_Import($params) {

  // API parameters
  if (!array_key_exists('form_id', $params)) {
    throw new API_Exception('Form ID is mandatory.', 1);
  }
  $form_id = $params['form_id'];
  
  $result = civicrm_api3('EnketoForm', 'getsingle', [
    'form_id' => $params['form_id'],
  ]);

  if (!array_key_exists('delay', $params)) {
    $delay = 1;
  }
  else {
    $delay= $params['delay'];
  }
  

  // --- Plugins parameters ---
  $manager_group = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_managers');  // If null don't send notifications
    
  $campaign = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_campaign');


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
  send_mail2group($manager_group, "admcrm@cncd.be", 
    "[CiviEnketo] Résultat d'importation ($form_id) : ".$importer->getNb_lines()." fiches",
    "<h1>Rapport d'importation des mandats</h1>".
    "<h2>".ts('Summary')."</h2>".
    $logs_summary.
    "<h2>".ts('Details')."</h2>".
    implode($logs_import, '<br>'));
      
  // Log timestamp in CiviCRM DB
  $result = civicrm_api3('EnketoForm', 'get', [
      'sequential' => 1,
      'form_id' => $form_id,
  ]);
  $id= $result['id'];
  
  $result = civicrm_api3('EnketoForm', 'create', [
      'last_importation_time' => date("Y-m-d H:i:s"),
      'id' => $id,
  ]);

  $returnValues = array("Form n.$form_id are imported succefuly."); // OK, success

  return civicrm_api3_create_success($returnValues, $params, 'NewEntity', 'NewAction');
}
