<?php
/*-------------------------------------------------------+
| CiviEnketo                                             |
| Copyright (C) 2018 CNCD-11.11.11                       |
| Author: Philippe Sampont                               |
| http://www.cncd.be/                                    |
+--------------------------------------------------------*/

require_once 'CRM/Civienketo/Importer/contact.php';
require_once 'CRM/Civienketo/Importer/mandate.php';
require_once 'CRM/Civienketo/Importer/bank_account.php';

class CRM_Civienketo_Importer_CNCD {

  protected $nb_lines;
  protected $nb_errors;
  protected $nb_new_contact;
  protected $nb_new_mandate;
  protected $nb_duplicate_IBAN;
  protected $count_notes;
  protected $timestamp_start;
  protected $timestamp_end;
  protected $logs;

  protected $mandates;
  protected $mandates_errors;
  protected $manager;

  function __construct($records) {
    $this->nb_lines = 0;
    $this->nb_errors = 0;
    $this->nb_new_contact = 0;
    $this->nb_new_mandate = 0;
    $this->nb_duplicate_IBAN = 0;
    $this->count_notes = 0;
    $this->logs = array();

    $this->mandates = $records;
    $this->mandates_errors = array();

    $this->manager = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_manager');
    if (!isset($this->manager) || $this->manager==0 ) $this->manager = 1;

    $this->campaign = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_campaign');
    if (!isset($this->campaign) || $this->campaign==0 ) $this->campaign = null;
  }

  function run() {
    $this->timestamp_start= date('d-m-Y H:i:s');
    
    $mandates = $this->mandates;
    foreach ($mandates as $mandate) {
      $this->nb_lines++;
 
      // Normalize data 
      $mandate["contact/lastname"]= ucfirst($mandate["contact/lastname"]);
      $mandate["contact/firstname"]= ucfirst($mandate["contact/firstname"]);
      $mandate["contact/city"]= ucfirst($mandate["contact/city"]);
      $contact= $mandate["contact/lastname"].", ".$mandate["contact/firstname"];
      if (isset($mandate['coord/iban_country'])) 
        $iban= $mandate['coord/iban_country'].$mandate['mandate_num/iban_checksum'].' ... '.substr($mandate['mandate_num/iban_account'],-4);
      else {
        if (isset($mandate['mandate0/iban_country'])) 
          $iban= $mandate['mandate0/iban_country'].$mandate['mandate_num/iban_checksum'].' ... '.substr($mandate['mandate_num/iban_account'],-4);
        else 
          if ($mandate['mandate0/bank_country']!='other')
            $iban= $mandate['mandate0/bank_country'].$mandate['mandate_num/iban_checksum'].' ... '.substr($mandate['mandate_num/iban_account'],-4);
          else
            $iban= substr($mandate['mandate_num_NC/iban_account_nc'],8);
      }
      $this->logs[] = ' - '.$this->nb_lines.' - <b>'.$contact.'</b> de '.$mandate['coord/city']. ' - '.$iban;

      $this->create_donor_or_mandate($mandate);
    }
  
    $this->timestamp_end= date('d-m-Y H:i:s');
  }

  /**
   * Create donor and mandate
   */
  function create_donor_or_mandate($mandate) {
    $contact_created = false;
    $mandate_created = false;
    
    $tx = new CRM_Core_Transaction();
    try {

      // Verify iban
      if (isset($mandate['coord/iban_country']))
        $iban= $mandate['coord/iban_country'].$mandate['mandate_num/iban_checksum'].$mandate['mandate_num/iban_account'];
      else {
        if (isset($mandate['mandate0/iban_country']))
          $iban= $mandate['mandate0/iban_country'].$mandate['mandate_num/iban_checksum'].$mandate['mandate_num/iban_account'];
        else
          if ($mandate['mandate0/bank_country']!="other")
            $iban= $mandate['mandate0/bank_country'].$mandate['mandate_num/iban_checksum'].$mandate['mandate_num/iban_account'];
          else
            $iban= $mandate['mandate_num_NC/iban_account_nc'];
      }
      $donor_id = get_account_contact($iban);
      if ($donor_id != 0) {
        $contact_info = get_contact($donor_id);
        if ($contact_info != 0) $nb_duplicate_IBAN++;
      }

      // In our case, always create a new donor, even it's a duplicate
      $contact_id = create_donor(
          $mandate['contact/firstname'], $mandate['contact/lastname'] ,
          $mandate['contact/langage'], $mandate['contact/civility'],
          $mandate['contact/birthdate'], 'KTB-'.$mandate['_uuid'], // must be unique 
          "tablet-".$mandate['imei'].'-'.$mandate['username'].'-'.$mandate['info/location'], $mandate['info/channel'],
          $mandate['info/no_phone'], $mandate['info/no_mail'], $mandate['info/no_email'], $mandate['info/no_mailing']); 
      $this->nb_new_contact++;
      $contact_created = true;

      add_home_address($contact_id,
          $mandate['coord/address'], 
          $mandate['coord/postalcode'], $mandate['coord/city'], 
          $mandate['coord/country']);    
      if (isset($mandate['coord/email']) && $mandate['coord/email'] != "") {
        add_home_mail($contact_id, $mandate['coord/email']);
      }
      if (isset($mandate['coord/phone']) && $mandate['coord/phone'] != "") {
        add_home_phone($contact_id, $mandate['coord/phone']);
      }
      if (isset($mandate['coord/mobile']) && $mandate['coord/mobile'] != "") {
        add_home_phone($contact_id, $mandate['coord/mobile'], "Mobile");
      }
      if (isset($mandate['info/remarks']) && $mandate['info/remarks'] != "") {
          create_donor_note($contact_id, "Note d'un ambassadeur" , $mandate['username'].'> '.$mandate['info/remarks']);
          $this->count_notes++;
          send_mail2contact($this->manager, "Note d'un ambassadeur", 
            "<p>".$mandate['username']." a écrit la note suivante : '".$mandate['info/remarks']."' dans la fiche de ce <a href='https://crm.cncd.be/civicrm/contact/view?reset=1&cid=".$contact_id."'>contact</a>.</p>".
             "<p>Merci de prendre les dispositions adéquates.<br>Civibot</p>");
      }
      
      // If IBAN is specified, create the mandate 
      if ((!isset($mandate['mandate_num/iban_account'])) || 
          (!isset($mandate['mandate_num/iban_checksum']))) {
        $this->nb_errors++; 
        $this->logs[] = "<font color=orange>WARNING : IBAN incorrect</font>";
        ksort($mandate);
        $mandate["warning"] = 'IBAN incorrect';
        $mandates_errors[$this->nb_errors]= $mandate;
      }
      else {
        $account_id =  create_bank_account($contact_id, $iban);
     
        if ($mandate["mandate/amount"]!="other") {
          $amount = $mandate["mandate/amount"];
        } else {
          $amount = $mandate["mandate/amount_other"];
        }

        $mandate_id = create_mandate(
          $contact_id, 
          $iban, $amount,
          $mandate["mandate/collect_day"], $mandate["end"], 
          'FRST', $mandate["username"], 
          null, $mandate["mandate/first_collect"], $this->campaign);
        $this->nb_new_mandate++; 
        $this->logs[] = "  Mandat de <font color=gold>$amount €</font> tous les ".$mandate["mandate/collect_day"]." de chaque mois. Fait à ".$mandate["info/location"];
        $mandate_created = true;
      }

      // Add in donors groups
      add_contact2group($contact_id, 1062);  // Mandats à vérifier
      if (isset($mandate['info/volunteer']) && $mandate['info/volunteer'] == 1) {
        add_contact2group($contact_id, 330);  // Récolte pour l'opération
      }
      if ($mandate_created) {
        $group_parent = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_group_parent');
        add_contact2group($contact_id, $group_parent); 
        if (isset($mandate['info/channel'])) {
          switch ($mandate['info/channel']) {
            case 'mail' :
              $group_email = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_group_email');
              add_contact2group($contact_id, $group_email); 
              add_contact2group($contact_id, 42); // Newsletter
              break;
            case 'postal' :
              $group_postal = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_group_postal');
              add_contact2group($contact_id, $group_postal); 
              break; 
          }
        }
      }
    }
    catch (Exception $e) {
      $errorMessage = $e->getMessage();
      ksort($mandate);
      $mandate["error"] = $errorMessage;
      $this->mandates_errors[$this->nb_errors]= $mandate;
      $this->nb_errors++;
      if ($contact_created) $this->nb_new_contact--;
      if ($mandate_created) $this->nb_new_mandate--;
      $this->logs[] = "<font color=red>ERROR : $errorMessage</font><br>Do rollback.";
      $this->logs[] = $e->getTrace();
      $tx->rollback();
    }
  }

  /**
   * Get logs of the last run
   */
  function getLog() {
    return $this->logs;
  }

  /**
   * Get summary of the last run
   */
  function getSummary() {
    $summary = '';

    $summary.= "<p>".ts("Start Date")." : ".$this->timestamp_start."</p>";
    $summary.= "$this->nb_lines fiches reçues : <br>";
    $summary.= "- $this->nb_new_contact contacts crées.<br>";
    $summary.= "- $this->count_notes notes créées.<br>";
    $summary.= "- $this->nb_new_mandate mandats créés. <br>";
    $summary.= "- $this->nb_duplicate_IBAN IBAN en double. <br>";
    $summary.= "Nombre d'erreurs rencontrées et annulées : $this->nb_errors.<br>";
    $summary.= "<p>".ts("End Date")." : ".$this->timestamp_end."</p>";

    return $summary;
  }
}
