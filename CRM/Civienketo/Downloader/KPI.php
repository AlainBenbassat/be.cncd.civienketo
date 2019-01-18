<?php
/*-------------------------------------------------------+
| CiviEnketo                                             |
| Copyright (C) 2018 CNCD-11.11.11                       |
| Author: Philippe Sampont                               |
| http://www.cncd.be/                                    |
+--------------------------------------------------------*/

class CRM_Civienketo_Downloader_KPI {

  protected $timestamp_start;
  protected $timestamp_end;
  protected $logs;

  protected $server_url;
  protected $token;
  protected $upload_dir;

  function __construct() {
    $this->logs = array();

    $this->server_url = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_server_url');
    if (!isset($this->server_url)) {
      CRM_Core_Error::fatal('Server_url not defined. Please configure this plugins.');
    }
    $this->token = CRM_Core_BAO_Setting::getItem('CiviEnketo Preferences', 'enketo_server_token');
    if (!isset($this->token)) {
      CRM_Core_Error::fatal('API token not defined. Please configure this plugins.');
    }

    $this->upload_dir = CRM_Core_Config::singleton()->uploadDir;
  }

  /***
   * Download records of yesterday via KPI
   */
  function run($form_id, $delay=1) {
    $this->timestamp_start= date('d-m-Y H:i:s');

    $today = date_create(date('Y-m-d'));
    $yesterday = date_sub($today, date_interval_create_from_date_string($delay." days"))->format("Y-m-d");
    $filename= $this->upload_dir.'enketo_data-'.$form_id.'-'.$yesterday.'.json';

    $api= "api/v1/data/";
    $query= '?query={"_submission_time":{"$gt":"'.$yesterday.'"}}';

    $opts = array(
      'http'=>array(
        'method'=>"GET",
        'header'=>"Content-Type: application/octet-stream\r\n".
                  "Authorization: Token ".$this->token."\r\n" 
      )
    );
    $context = stream_context_create($opts);
 
    $this->logs[] = ts('- Downloading form n.').' '.$form_id.'... ';

    $data = file_get_contents($this->server_url.$api.$form_id.$query, false, $context);
    $size = strlen($data);
    file_put_contents($filename, $data);
    if ($size <= 2) { 
      CRM_Core_Session::setStatus(ts('Downladed file is empty.'), ts('Import warning'), 'warn');
    }
  
    $this->logs[] = '- '.ts('Server url: ').$this->server_url.$api.$form_id.$query;
    $this->logs[] = '- '.ts('File downloaded: ').$filename;
    $this->logs[] = '- '.ts('File size: ').$size." b";
    
    $records = json_decode($data, true);

    $this->timestamp_end= date('d-m-Y H:i:s');

    return $records;
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
    $summary.= "<p>".ts("End Date")." : ".$this->timestamp_end."</p>";

    return $summary;
  }
}
