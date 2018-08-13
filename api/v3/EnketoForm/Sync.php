<?php
use CRM_Civienketo_ExtensionUtil as E;

/**
 * EnketoForm.Sync API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_enketo_form_Sync_spec(&$spec) {
  $spec['magicword']['api.required'] = 1;
}

/**
 * EnketoForm.Sync API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_enketo_form_Sync($params) {
  if (array_key_exists('magicword', $params) && $params['magicword'] == 'sesame') {
    $server_url = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_server_url');
    $token = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_server_token');
    $api= "api/v1/";
    $opts = array(
      'http'=>array(
        'method'=>"GET",
        'header'=>"Content-Type: application/octet-stream\r\n".
                "Authorization: Token ".$token."\r\n" 
      )
    );

    // Get list of forms from API
    $context = stream_context_create($opts);
    $request = $server_url.$api.'forms';
    $forms = json_decode(file_get_contents($request, false, $context));

    // Add each form in database
    $nb_forms = 0;
    foreach($forms as $form) {
      $nb_forms++;

      // Create or update Enketo form
      $result = civicrm_api3('EnketoForm', 'get', [
        'form_id' => $form->formid,
      ]);
      if (!isset($result['id'])) {
//        echo "Form n°$nb_forms [NEW] : $form->title\n";
        // Get enketo form URL
        $request = $server_url.$api.'forms/'.$form->formid.'/enketo';
        $result = json_decode(file_get_contents($request, false, $context));
        $enketo_url = $result->enketo_url;

        $result = civicrm_api3('EnketoForm', 'create', [
          'form_id' => $form->formid,
          'title' => $form->title,
          'downloadable' => $form->downloadable,
          'url' => $form->url,
          'data_url' => $server_url.$api.'data/'.$form->formid,
          'enketo_url' => $enketo_url,
          'num_of_submissions' => $form->num_of_submissions,
          'last_submission_time' => $form->last_submission_time,
        ]);
      }
      else {
//        echo "Form n°$nb_forms [UPDATED] : $form->title\n";
        $id = $result['id'];
        $result = civicrm_api3('EnketoForm', 'create', [
          'id' => $id,
          'title' => $form->title,
          'downloadable' => $form->downloadable,
          'num_of_submissions' => $form->num_of_submissions,
          'last_submission_time' => $form->last_submission_time,
        ]);    
      }
    }

    $returnValues = array("$nb_forms forms are updated."); // OK, success

    return civicrm_api3_create_success($returnValues, $params, 'NewEntity', 'NewAction');
  }
  else {
    throw new API_Exception('Everyone knows that the magicword is "sesame"', 1234);
  }
}
