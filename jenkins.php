<?php
require __DIR__ . '/vendor/autoload.php';
require_once 'settings.php';
require_once 'src/jenkins.php';
require_once 'src/stash.php';

$stash = new stash();
$reference_repos = $stash->getReferenceRepoList();
$projects = array();
foreach ($reference_repos as $name => $repo) {
  $branches = $stash->getBranches($name);
  foreach ($branches as $branch) {
    $pattern = "/[^0-9a-z-_()]/";
    $output = preg_replace($pattern, "_", $name . '-(' . $branch->displayId . ')');
    $projects[$name] = array(
      'name' => $name,
      'repo' => $repo->cloneUrl,
      'branch' => $branch->displayId,
      'job_name' => $output,
    );
  }
}

$jenkins = new jenkins();
foreach ($projects as $job_name => $replacements) {
    $jenkins->setJob($replacements['job_name'], 'config', $replacements);
}

