<?php
/**
  Manage newsletter
*/

/***
  Subscribe a contact uid to the newsletter
*/
function subscribe_to_newsletter($uid) {
  \Civi\Api4\Contact::update(FALSE)
    ->addValue('Newsletters.Newsletter_g_n_rale', 1)
    ->addWhere('id', '=', $uid)
    ->execute();
}

/***
  Subscribe a contact uid to the Actu newsletter
*/
function subscribe_to_regional_newsletter($uid, $country, $postalcode) {
  if (($country!='BE') or ($postalcode == NULL)) {
    return;
  }

  $field = '';
  if (($postalcode > 0) and ($postalcode < 1299)) {
    // Bruxelles
    $field = 'Agendas_r_gionaux.ActuBrux';
  }
  elseif ($postalcode < 1499) {
    // Brabant Wallon
    $field = 'Agendas_r_gionaux.ActuBW';
  }
  elseif ($postalcode < 1999) {
    // Vlaams-Brabant
  }
  elseif ($postalcode < 2999) {
    // Antwerpen
  }
  elseif ($postalcode < 3499) {
    // Vlaams-Brabant
  }
  elseif ($postalcode < 3999) {
    // Limbourg
  }
  elseif ($postalcode < 4999) {
    // Liège
    $field = 'Agendas_r_gionaux.ActuLi_ge';
  }
  elseif ($postalcode < 5999) {
    // Namur
    $field = 'Agendas_r_gionaux.ActuNam';
  }
  elseif ($postalcode < 6599) {
    // Hainaut
    $field = 'Agendas_r_gionaux.ActuHainaut';
  }
  elseif ($postalcode < 6999) {
    // Luxembourg
    $field = 'Agendas_r_gionaux.ActuLux';
  }
  elseif ($postalcode < 7999) {
    $field = 'Agendas_r_gionaux.ActuHainaut';
  }
  elseif ($postalcode < 8999) {
    // West-Vlaanderen
  }
  elseif ($postalcode < 9999) {
    // Oost-Vlanderen
  }

  if ($field) {
    \Civi\Api4\Contact::update(FALSE)
      ->addValue($field, 1)
      ->addWhere('id', '=', $uid)
      ->execute();
  }
}
