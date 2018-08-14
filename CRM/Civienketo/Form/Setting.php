<?php

use CRM_Civienketo_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Civienketo_Form_Setting extends CRM_Admin_Form_Setting {
  public function buildQuickForm() {
    CRM_Utils_System::setTitle(ts('CiviEnketo - Settings', array('domain' => 'be.cncd.civienketo')));

    // Server Settings
    $server_url = $this->add(
      'text',
      'server_url',
      ts('Server URL'),
      ""
    );
    $server_url->setValue(CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_server_url'));

    $server_token = $this->add(
      'text',
      'server_token',
      ts('Server token'),
      ""
    );
    $server_token->setValue(CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_server_token'));


    // Manager to send emails
    $this->addEntityRef(
      'manager', 
      ts('Manager'), 
      array('create' => TRUE), 
      TRUE
    );
    $manager = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_manager'); 
    $this->getElement('manager')->setValue($manager); 

    $this->addEntityRef('managers', ts('Managers'), array(
          'entity' => 'group',
          'placeholder' => ts('- Select a group'),
          'select' => array('minimumInputLength' => 0),
        ),TRUE);
    $managers = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_managers'); 
    $this->getElement('managers')->setValue($managers); 

    // Campaign
    $this->addEntityRef('campaign', ts('Campaign'), array(
          'entity' => 'campaign',
          'placeholder' => ts('- Select a campaign'),
          'select' => array('minimumInputLength' => 3),
        ),TRUE);
    $campaign = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_campaign'); 
    $this->getElement('campaign')->setValue($campaign); 

    // Mail settings

    $template1= $this->addEntityRef('template1', ts('Welcome lettre'), array(
          'entity' => 'messageTemplate',
          'placeholder' => ts('- Select a template'),
          'select' => array('minimumInputLength' => 0),
          'api' => array(
              'search_field' => 'msg_title',
              'label_field' => 'msg_title',
          ),
        ),TRUE);
    $template1->setValue(CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_template1')); 
    $template1->freeze();

    $template2 = $this->addEntityRef('template2', ts('Mandate copy'), array(
          'entity' => 'messageTemplate',
          'placeholder' => ts('- Select a template'),
          'select' => array('minimumInputLength' => 0),
          'api' => array(
              'search_field' => 'msg_title',
              'label_field' => 'msg_title',
          ),
        ),FALSE);
    $template2->setValue(CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_template2')); 
    $template2->freeze();

    $send_ack = $this->add('advcheckbox', 'send_ack', ts('Send a confirmation email ?'));
    $send_ack->setValue(CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_send_ack')); 
    $send_ack->freeze();

    // Log level
    $this->add('advcheckbox', 'verbose', ts('Verbose'));
    $verbose = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_verbose'); 
    $this->getElement('verbose')->setValue($verbose); 

/*
    // is production server
    $this->addElement(
      'checkbox',
      'is_production',
      ts('Is production server ?'),
      '');
 */
    // Groups 
    $this->addEntityRef('group_parent', ts('Parent group'), array(
          'entity' => 'group',
          'placeholder' => ts('- Select a group'),
          'select' => array('minimumInputLength' => 0),
        ),TRUE);
    $group_parent = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_group_parent'); 
    $this->getElement('group_parent')->setValue($group_parent); 

    $this->addEntityRef('group_email', ts('Group for emails'), array(
          'entity' => 'group',
          'placeholder' => ts('- Select a group'),
          'select' => array('minimumInputLength' => 0),
        ),TRUE);
    $group_email = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_group_email'); 
    $this->getElement('group_email')->setValue($group_email); 
    
    $this->addEntityRef('group_postal', ts('Group for postal'), array(
          'entity' => 'group',
          'placeholder' => ts('- Select a group'),
          'select' => array('minimumInputLength' => 0),
        ),TRUE);
    $group_postal = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_group_postal'); 
    $this->getElement('group_postal')->setValue($group_postal); 

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    CRM_Core_BAO_Setting::setItem($values['server_url'], 'CiviEnketo Preferences', 'enketo_server_url');
    CRM_Core_BAO_Setting::setItem($values['server_token'], 'CiviEnketo Preferences', 'enketo_server_token');
    CRM_Core_BAO_Setting::setItem($values['manager'], 'CiviEnketo Preferences', 'enketo_manager');
    CRM_Core_BAO_Setting::setItem($values['managers'], 'CiviEnketo Preferences', 'enketo_managers');
    CRM_Core_BAO_Setting::setItem($values['verbose'], 'CiviEnketo Preferences', 'enketo_verbose');
    CRM_Core_BAO_Setting::setItem($values['send_ack'], 'CiviEnketo Preferences', 'enketo_send_ack');
    CRM_Core_BAO_Setting::setItem($values['group_parent'], 'CiviEnketo Preferences', 'enketo_group_parent');
    CRM_Core_BAO_Setting::setItem($values['group_email'], 'CiviEnketo Preferences', 'enketo_group_email');
    CRM_Core_BAO_Setting::setItem($values['group_postal'], 'CiviEnketo Preferences', 'enketo_group_postal');
    CRM_Core_BAO_Setting::setItem($values['campaign'], 'CiviEnketo Preferences', 'enketo_campaign');
    CRM_Core_BAO_Setting::setItem($values['template1'], 'CiviEnketo Preferences', 'enketo_template1');
    CRM_Core_BAO_Setting::setItem($values['template2'], 'CiviEnketo Preferences', 'enketo_template2');

    CRM_Core_Session::setStatus(E::ts('Data updated.'));
    //parent::postProcess();
    CRM_Core_DAO::triggerRebuild();

   CRM_Core_Session::singleton()->replaceUserContext(CRM_Utils_System::url('civicrm/admin/setting/enketo'));
  }


  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
