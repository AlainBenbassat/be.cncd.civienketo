<?php
/**
  Manage newsletter
*/

define("NEWSLETTER_GROUP", 42);
define("ACTUBRUX", 317);
define("ACTUBW", 318);
define("ACTUHainaut", 319);
define("ACTULiège", 316);
define("ACTULUX", 315);
define("ACTUNAM", 284);

/***
  Subscribe a contact uid to the newsletter
*/
function subscribe_to_newsletter($uid) {
  $result = civicrm_api3('groupContact', 'create', array(
      'sequential' => 1,
      'contact_id' => $uid,
      'group_id' => NEWSLETTER_GROUP,
  ));

  return $result;
}

/***
  Subscribe a contact uid to the Actu newsletter
*/
function subscribe_to_regional_newsletter($uid, $country, $postalcode) {
  if (($country!='BE') or ($postalcode == NULL)) return;
 
  if (($postalcode > 0) and ($postalcode < 1299)) {
    $group = ACTUBRUX;               // 'Bruxelles';
  } else if ($postalcode < 1499) {
    $group = ACTUBW;               // 'Brabant Wallon';
  } else if ($postalcode < 1999) {
    $group = NULL;               // 'Vlaams-Brabant';
  } else if ($postalcode < 2999) {
    $group = NULL;               // 'Antwerpen';
  } else if ($postalcode < 3499) {
    $group = NULL;               // 'Vlaams-Brabant';
  } else if ($postalcode < 3999) {
    $group = NULL;               // 'Limbourg';
  } else if ($postalcode < 4999) {
    $group = ACTULiège;               // 'Liège';
  } else if ($postalcode < 5999) {
    $group = ACTUNAM;               // 'Namur';
  } else if ($postalcode < 6599) {
    $group = ACTUHainaut;               // 'Hainaut';
  } else if ($postalcode < 6999) {
    $group = ACTULUX;               // 'Luxembourg'; 
  } else if ($postalcode < 7999) {
    $group = ACTUHainaut;               // 'Hainaut';
  } else if ($postalcode < 8999) {
    $group = NULL;               // 'West-Vlaanderen';
  } else if ($postalcode < 9999) {
    $group = NULL;               // Oost-Vlanderen
  } else
    $group = NULL;

  if ($group != NULL) {
    $result = civicrm_api3('groupContact', 'create', array(
        'sequential' => 1,
        'contact_id' => $uid,
        'group_id' => $group,
    ));
  }
  return $result;

}
