<?php
use CRM_Civienketo_ExtensionUtil as E;

class CRM_Civienketo_BAO_EnketoForm extends CRM_Civienketo_DAO_EnketoForm {

  /**
   * Create a new EnketoForm based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Civienketo_DAO_EnketoForm|NULL
   *
  public static function create($params) {
    $className = 'CRM_Civienketo_DAO_EnketoForm';
    $entityName = 'EnketoForm';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
