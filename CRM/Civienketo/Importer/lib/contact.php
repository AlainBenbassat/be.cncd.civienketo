<?php
/**
  Contact management in CiviCRM
*/

/***
  Get contact 
*/
function get_contact($id) {

  $result = civicrm_api3('Contact', 'get', array(
    'sequential' => 1,
    'id' => $id, 
  ));


  if (!isset($result['count']) || $result['count'] == 0) {
    return 0;
  }
  else {
    return $result['values'][0];
  }
}

/***
  Find contact by external id
*/
function find_contact_by_external_id($eid) {

  $result = civicrm_api3('Contact', 'get', array(
    'sequential' => 1,
    'external_identifier' => $eid, 
  ));


  if (!isset($result['count']) || $result['count'] == 0) {
    return 0;
  }
  else {
    return $result['id'];
  }
}

/***
  Check for duplicate donor on the IBAN
*/
function check_duplicate_donor($iban) {

  $result = civicrm_api3('Contact', 'get', array(
    'sequential' => 1,
//    'custom_1' => $iban,
    'external_identifier' => $iban,   // IBAN in external_identifier is unique
    'return' => "id",
  ));


  if (!isset($result['count']) || $result['count'] == 0) {
    return 0;
  }
  else {
    return $result['id'];
  }
}


/*** 
  Create a new donor 
*/
function create_donor($firstname, $lastname, $language, $gender, $birthdate, $external_id=NULL, $source=NULL, $preferred_com=NULL) {

  if (!isset($source)) $source = "Script ".__FILE__;

  // Transcodages


  switch ($language) {
    case 'FR': 
      $language= 'fr_FR';
      break;
    case 'NL': 
      $language= 'nl_NL';
      break;
    case 'EN':
      $language= 'en_GB';
      break;
  }
  switch ($gender) {
    case 'F': 
    case 'Féminin': 
      $gender= 'Female';
      break;
    case 'M': 
    case 'Masculin': 
      $gender= 'Male';
      break;
  }
  if (isset($birthdate) && $birthdate!=NULL) { 
    try {
      #$myDateTime = DateTime::createFromFormat('d/m/Y', $birthdate);
      $myDateTime = DateTime::createFromFormat('Y-m-d', $birthdate);
      if ($myDateTime)
        $birth_date = $myDateTime->format('Y/m/d');
    }
    catch (Exception $e) {
      $birth_date = NULL;
    }
  }
  else $birth_date=NULL;
  switch ($preferred_com) {
    case 'courriel':
    case 'mail':
      $preferred_com = 'Email';
      break;
    case 'postal':
      $preferred_com = 'Postal Mail';
      break;
    default:
      $preferred_com = NULL;
      break;
  }


  $contact = array(
    'first_name' => $firstname,
    'last_name' => $lastname,
    'contact_type' => 'Individual',
    'contact_sub_type' => 'Donateur',
    'preferred_language' => $language,
    'gender_id' => $gender,
    'birth_date' => $birth_date,
    'source' => $source,
    'do_not_trade' => 1,
    'external_identifier' => $external_id,
    'preferred_communication_method' => $preferred_com,
  );


  $result = civicrm_api3('Contact', 'create', $contact);

  return $result['id'];
}

/*
  Get the contact list of a group

  Return: array
    list of contacts in the group
*/
function get_contacts_from_group($group_name) {

  
  $result = civicrm_api3('groupContact', 'get', array(
      'sequential' => 1,
      'group_id' => $group_name
  ));
  
  return $result['values'];
}

/*
   Add a contact to a static group
*/
function add_contact2group($uid, $group_name) {

  $result = civicrm_api3('groupContact', 'create', array(
      'sequential' => 1,
      'contact_id' => $uid,
      'group_id' => $group_name
  ));
  
}

/*
   Remove a contact from a static group
*/
function remove_contact_from_group($uid, $group_name) {

  
  $result = civicrm_api3('groupContact', 'create', array(
      'sequential' => 1,
      'contact_id' => $uid,
      'group_id' => $group_name,
      'status' => 'Removed',
  ));
  
}

/*
   Add a home email to the user $uid
*/
function add_home_address($uid, $street, $postalcode, $city, $country) {
  
  // Transcodages
  switch (strtolower($country)) {
    case 'autriche' :
    case 'at':
      $country_id = 1014;
      break;
    case 'allemagne' :
    case 'de':
      $country_id = 1082;
      break;
    case 'belgique' :
    case 'be' :
      $country_id = 1020;
      break;
    case 'espagne' :
    case 'spain' :
    case 'es':
      $country_id = 1198;
      break;
    case 'france' :
    case 'fr':
      $country_id = 1076;
      break;
    case 'luxembourg' :
    case 'lu':
      $country_id = 1126;
      break;
    case 'pologne' :
    case 'poland' :
    case 'pl':
      $country_id = 1172;
      break;
    case NULL :
    default: 
      break;
      throw new Exception('Unknow country.');   
  }
  if ($postalcode < 1299) {
     $province_id = 5217;		// 'Bruxelles';
  } else if ($postalcode < 1499) {
     $province_id = 1786;		// 'Brabant Wallon';
  } else if ($postalcode < 1999) {
     $province_id = 1793;		// 'Vlaams-Brabant';
  } else if ($postalcode < 2999) {
     $province_id = 1785;		// 'Antwerpen';
  } else if ($postalcode < 3499) {
     $province_id = 1793;		// 'Vlaams-Brabant';
  } else if ($postalcode < 3999) {
     $province_id = 1789;		// 'Limbourg';
  } else if ($postalcode < 4999) {
     $province_id = 1788;		// 'Liège';
  } else if ($postalcode < 5999) {
     $province_id = 1791;		// 'Namur';
  } else if ($postalcode < 6599) {
     $province_id = 1787;		// 'Hainaut';
  } else if ($postalcode < 6999) {
     $province_id = 1790;		// 'Luxembourg';	
  } else if ($postalcode < 7999) {
     $province_id = 1789;		// 'Hainaut';
  } else if ($postalcode < 8999) {
     $province_id = 1794;		// 'West-Vlaanderen';
  } else if ($postalcode < 9999) {
     $province_id = 1792;		// Oost-Vlanderen
  }

  $result = civicrm_api3('Address', 'create', array(
      'sequential' => 1,
      'contact_id' => $uid,
      'location_type_id' => "Domicile",
      'street_address' => $street,
      'postal_code' => $postalcode,
      'city' => $city,
      'state_province_id' => $province_id,    
      'country_id' => $country_id,    
  ));


  return $result['id'];
}

/*
   Add an email address to the user $uid
*/
function add_home_mail($uid, $email) {
  
  
  $result = civicrm_api3('Email', 'create', array(
      'sequential' => 1,
      'contact_id' => $uid,
      'email' => $email,
      'location_type_id' => "Domicile",
    ));
  
  return $result['id'];
}

/*
   Add an home phone to the user $uid
*/
function add_home_phone($uid, $phone, $phone_type="Phone") {
  
  $result = civicrm_api3('Phone', 'create', array(
      'sequential' => 1,
      'contact_id' => $uid,
      'phone' => $phone,
      'location_type_id' => "Domicile",
      'phone_type_id' => $phone_type,
    ));
  
  return $result['id'];
}

/**
  Send a email to a contact
*/
function send_mail2contact($contact_id, $subject, $message, $file=null, $sender='donateurs@cncd.be') {
  require_once('libphp-phpmailer/class.phpmailer.php');

  $email = new PHPMailer();
  $email->setFrom($sender, 'CNCD-11.11.11');
  $email->Subject = $subject;
  $email->CharSet = 'UTF-8';
  $email->msgHTML($message);

  // Recipients
  $result = civicrm_api3('Contact', 'get', array(
    'sequential' => 1,
    'id' => $contact_id, 
  ));
  if ((!isset($result['count']) || $result['count'] != 1)) {
     return -1;
  }
  $contact= $result['values'][0];
  if ((!isset($contact['email']) || $contact['email'] == '')) {
    return -2;
  }
  $email->addAddress($contact['email']);

  if ($file) $email->addAttachment($file);
  
  return $email->send();
}

function create_donor_note($uid, $subject, $text) {

  $result = civicrm_api3('Note', 'create', array(
      'sequential' => 1,
      'entity_table' => "civicrm_contact",
      'entity_id' => $uid,
      'subject' => $subject,
      'note' => $text,
    ));


  return $result['id'];
}

/***
 * Send mail to contacts of a group
 * (Limited to 3 contacts)
 */
function send_mail2group($group_id, $from, $subject, $message) {
  $result = civicrm_api3('GroupContact', 'get', array(
      'sequential' => 1,
      'group_id' => $group_id,
  ));
  if (!isset($result['count']) || $result['count'] == 0) return 0;
  if ($result['count'] > 3) return -1; // Anti massive leak precaution

  $count = 0;
  foreach($result['values'] as $contact) {
    $count++;
    $contact_id = $contact['contact_id'];
    $result = civicrm_api3('Contact', 'get', array(
      'sequential' => 1,
      'id' => $contact_id, 
    ));

    $recipient = $result['values'][0]['email'];
    $mailParams = array(
        'from' => $from,
        'toName' => 'Enketo Manager',
        'toEmail' => $recipient,
        'subject' =>  $subject,
        'html' => $message,
    );

  //echo ("Email $recipient<br>");
  $result_send = CRM_Utils_Mail::send($mailParams);
  }

  return $count;
}
?>
