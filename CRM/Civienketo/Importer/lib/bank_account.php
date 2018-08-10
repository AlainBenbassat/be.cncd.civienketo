<?php
/**
 Bank account management in CiviCRM
*/

/*
  Check if account exist

  @return 1 if exist
*/
function check_bank_account($iban)
{
  $result = civicrm_api3('BankingAccountReference', 'getcount', array(
    'sequential' => 1,
    'reference' => $iban,
  ));

  return $result;
}

/*
  Retrieve contact of a bank account

  @return contact_id
*/
function get_account_contact($iban)
{
  // get reference
  $result = civicrm_api3('BankingAccountReference', 'get', array(
    'sequential' => 1,
    'reference' => $iban,
  ));
  if ($result['values'] == null) return 0;
 
  $account_id = $result['values']['0']['ba_id'];

  $result = civicrm_api3('BankingAccount', 'getsingle', array(
    'sequential' => 1,
    'id' => $account_id,
  ));
  
  return $result['contact_id'];
}

/*
  Add a bank account for the contact
*/
function create_bank_account($contact_id, $iban, $bic=NULL, $creation_date=NULL) {
  $now = date('Y-m-d H:i:s');
  if ($creation_date==NULL) $creation_date=$now;

  if (($bic==NULL) and ((substr($iban, 0,2) == 'BE') or (substr($iban,0,2) == 'AT') or (substr($iban,0,2) == 'DE') or (substr($iban,0,2) == 'CH') or (substr($iban,0,2) == 'PL'))) {
    // Find BIC from the IBAN
      $result = civicrm_api3('Bic', 'getfromiban', array(
        'iban' => $iban,
      ));
    $bic= $result['bic'];
    
    $data_parsed = '{"BIC":"'.$bic.'","country":"'.substr($iban,0,2).'","name":"'.$result['title'].'"}';
  }
  else {
    $data_parsed = '{"BIC":"'.$bic.'","country":"'.substr($iban,0,2).'"}';
  }  

  $account = array(
    "contact_id" => $contact_id,
    'data_parsed' => $data_parsed, 
    'created_date' => $creation_date,
  );
  
  $result = civicrm_api3('BankingAccount', 'create', $account);
  
  $account_id = $result['id'];

  $account_ref = array(
    'ba_id' => $account_id,
    'reference_type_id' => "5915",      // /!\ Depend of your database /!\
    'reference' => $iban,
  );
  
  $result = civicrm_api3('BankingAccountReference', 'create', $account_ref);

  return $account_id;
}
?>
