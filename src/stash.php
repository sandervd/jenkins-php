<?php

use GuzzleHttp\Client;
class stash {
  /**
   * @var Client GuzzleClient
   */
  protected $conn = NULL;

  function __construct()
  {
    $this->conn = new Client([
      'base_uri' => STASH_HOST,
      'timeout' => 5.0,
    ]);
    $this->auth = [STASH_USERNAME, STASH_PASSWORD];
  }

  private function fetchPaged($uri) {
    $last_page = FALSE;
    $items_fetched = 0;
    $items = array();
    while (!$last_page) {
      /** @var \GuzzleHttp\Psr7\Response $resp */
      $resp = $this->conn->request('GET', $uri . '?start=' . $items_fetched, ['auth' => $this->auth]);
      if ($resp->getStatusCode() != 200) {
        return FALSE;
      }
      $body = (string) $resp->getBody();
      $return_object = json_decode($body);

      $items_fetched = $items_fetched + $return_object->size;
      $items = array_merge($items, $return_object->values);
      $last_page = (bool) $return_object->isLastPage;
    }
    return $items;
  }

  function getRepoList() {
    $uri = 'rest/api/1.0/projects/' . STASH_PROJECT . '/repos';
    return $this->fetchPaged($uri);
  }

  function getReferenceRepoList() {
    $reference_repos = array();
    foreach($this->getRepoList() as $repo) {
      $name = $repo->name;
      $name_parts = explode('-', $name);

      if (array_pop($name_parts) == 'reference') {
        $reference_repos[$name] = $repo;
      }
    }
    return $reference_repos;
  }

  function getBranches($repo_name) {
    $uri = 'rest/api/1.0/projects/' . STASH_PROJECT . '/repos/' . $repo_name .'/branches';
    return $this->fetchPaged($uri);
  }

}