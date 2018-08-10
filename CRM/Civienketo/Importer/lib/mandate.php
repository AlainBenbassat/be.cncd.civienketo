<?php
/***
 Mandate management CiviCRM
***/

/*
  Create a mandate for the contact
*/
function create_mandate($contact_id, $iban, $amount, $day, $signdate, $status='FRST', $source=NULL, $ref=NULL, $startdate=NULL, $campaign=NULL) {

  if ($amount==NULL || $amount==0 || $amount[0]=='€')
      throw new Exception('Amount of mandate is null.');

  $now = date('Y-m-d H:i:s');
  if (!isset($start_date) || $start_date==NULL) $start_date=$now;

  // Transcodages
  switch ($status) {
    case 'Pause':
      $status= 'COMPLETE';
      break;
    case 'FRST' :
    case 'En Attente' :
      $status= 'FRST';
      break;
    case 'RCUR' :
    case 'En cours' :
    case 'En Cours' :
    case 'Refus' :
      $status= 'RCUR';
      break;
    default:
      throw new Exception('Unknow mandate status');
  }

/*  if (isset($signdate) && $signdate!=null) { 
    $myDateTime = DateTime::createFromFormat('Y-m-d', $signdate);
    $sign_date = $myDateTime->format('Y/m/d');
  }
  else $sign_date=null;
*/
  if (isset($startdate) && $startdate!=null) { 
//    $myDateTime = DateTime::createFromFormat('Y-m-d', $startdate);
//    $startdate = $myDateTime->format('Y/m/d');
  }
  else $startdate=$signdate;

  /* --- Create mandate --- */
  // Find BIC
  if (substr($iban,0, 2) == 'BE') {
    $result = civicrm_api3('Bic', 'getfromiban', array(
        'iban' => $iban,
      ));
  
    $bic= $result['bic'];
  }
  
  $mandate = array(
    "contact_id" => $contact_id,
    "type" => "RCUR",
    "status" => $status,
    "iban" => $iban,
    "bic" => $bic,
    "amount" => $amount,
    "source" => $source,
    'reference' => $ref,
    "creation_date" => $now,
    "is_enabled" => 1,
    "creditor_id" => 1,
#    "financial_type_id" => "Don",
    "financial_type_id" => 1,
    "cycle_day" => $day,
    "date" => $signdate,
    "start_date" => $startdate,
    "frequency_interval" => 1,
    "frequency_unit" => 'month',
    "campaign_id" => $campaign,
  );
  

  $result = civicrm_api3('SepaMandate', 'createfull', $mandate);
  $mandate_id = $result['id'];
  
  return $mandate_id;
}
?>
