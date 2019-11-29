<?php
/***
 Contributions management in CiviCRM
***/

/*
  Add a contribution for a contact
*/
function create_donation($contact_id, $amount, $date, $source, $note) {
  global $logger;

  $logger->log("  create contribution for donator $contact_id...");
  
  $contrib = array(
    'contact_id' => $contact_id,
    'financial_type_id' => 1,
    'total_amount' => $amount,
    'receive_date' => $date,
    'contribution_status_id' => 1,  // 2 = "Pending" / 5 = "In Progress"
    'source' => $source,
    'note' => $note,
    //'skipRecentView' => 1,
  );
  
  $result = civicrm_api3('Contribution', 'create', $contrib);
  
  $logger->debug($result);

  $logger->log("  ... contribution".$result['id']." creaded."); 
}
/*
  Add a pending contribution for a contact
*/
function create_contribution_for_mandate($mandate_ref, $month=NULL, $year=NULL, $status="Pending") {
  global $log ;

  /* --- Default values --- */
  if (!isset($month) || $month==NULL) $month=date('m');
  if (!isset($year) || $year==NULL) $year=date('Y');

  /* --- Search mandate --- */
  if ($log > 0) print("  search mandate n.".$mandate_ref." ... \n");
  $result = civicrm_api3('SepaMandate', 'get', array(
      'sequential' => 1,
      'reference' => $mandate_ref,
  ));

  if ($log > 1) print_r($result);
  $mandate = $result['values'][0];

  /* --- Search recurent contribution --- */
  if ($log > 0) print("  search recurent contribution for mandate n.".$mandate["id"]." ... \n");
  $result = civicrm_api3('ContributionRecur', 'get', array(
      'sequential' => 1,
      'id' => $mandate["entity_id"],
  ));
  if ($log > 1) print_r($result);
  $recur_contrib = $result['values'][0];
  $date = $year.'-'.$month.'-'.$recur_contrib['cycle_day']. ' 00:00:00';

  /* --- Create contribution --- */
  $contrib = array(
    'contact_id' => $mandate['contact_id'],
    'financial_type_id' => $recur_contrib['financial_type_id'],
    'contribution_recur_id' => $mandate["entity_id"],
    'total_amount' => $recur_contrib["amount"],
    'receive_date' => $date,
    'currency' => $recur_contrib["currency"],
    'contribution_status_id' => $status,  // 2 = "Pending" / 5 = "In Progress"
    'source' => $mandate["source"],
    'skipRecentView' => 1,
  );
  
  if ($log > 0) print("  creating pending contribution : ".$contrib['total_amount']." ".$recur_contrib["currency"]." ... \n");

  $result = civicrm_api3('Contribution', 'create', $contrib);
  
  if ($log > 1) print_r($result);
  if ($log > 0) print("  ... contribution n.".$result['id']." created.\n");


  return $result['id'];
}
?>
