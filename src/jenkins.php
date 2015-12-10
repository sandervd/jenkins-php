<?php

use GuzzleHttp\Client;
class jenkins
{
  /**
   * @var Client GuzzleClient
   */
  protected $conn = NULL;
  protected $jobs = NULL;

  function __construct()
  {
    $this->conn = new Client([
      'base_uri' => JENKINS_HOST,
      'timeout' => 5.0,
    ]);
  }

  function getJobs()
  {
    if (!empty($this->jobs)) {
      return $this->jobs;
    }
    $resp = $this->conn->get('api/json');
    $body = json_decode($resp->getBody());
    $this->jobs = array();
    foreach ($body->jobs as $job) {
      $this->jobs[$job->name] = $job->url;
    }
    return $this->jobs;
  }

  function setJob($name, $template, $replacements = array())
  {
    $job_xml = file_get_contents('templates/' . $template . '.xml', 'r');
    foreach ($replacements as $search => $replace) {
      $job_xml = str_replace('{{{' . $search . '}}}', $replace, $job_xml);
    }
    $job = new SimpleXMLElement($job_xml);
    $job->displayName = $name;

    // Existing job.
    if (isset($this->getJobs()[$name])) {
      return $this->conn->post('job/' . $name . '/config.xml', [
        'body' => $job->asXml(),
        'headers' => [
          'Content-Type' => 'application/xml',
        ],
      ]);
    }
    // New job.
    return $this->conn->post('createItem', [
      'body' => $job->asXml(),
      'headers' => [
        'Content-Type' => 'application/xml',
      ],
      'query' => ['name' => $name]
    ]);

  }
}
