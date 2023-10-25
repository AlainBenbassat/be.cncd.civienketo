<?php
  // --- Bootstrap ---
  require_once '/var/www/sites/all/modules/civicrm/civicrm.config.php';
  require_once 'CRM/Core/Config.php';
  $config = CRM_Core_Config::singleton();
  require_once '/var/www/sites/all/modules/civicrm/api/class.api.php';

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
        echo "Form n°$nb_forms [NEW] : $form->title\n";
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
        echo "Form n°$nb_forms [UPDATED] : $form->title\n";
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

