<?php
use CRM_Civienketo_ExtensionUtil as E;

class CRM_Civienketo_Page_EnketoForms extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(E::ts('EnketoForms'));

    $result = civicrm_api3('EnketoForm', 'getlist', [
      'extra' => ["id", "form_id", "title", "url", "data_url", "enketo_url", "num_of_submissions", "last_submission_time", "last_importation_time", "downloadable"],]);

    $forms = $result['values'];
    foreach($forms as &$form) {
      $form['extra']['data_url'] = $form['extra']['data_url'].'.csv';
    }
    //CRM_Core_Error::Debug('Result', $forms, $log = true, $html= true);

    $this->assign('forms', $forms);


    $this->assign('last_sync_timestamp', date('Y-m-d H:i:s'));

    parent::run();
  }

}
