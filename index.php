<?php
  include('config.inc');

  header('Content-Type: application/json');

  $now = DateTime::createFromFormat('U.u', microtime(true));
  $time = $now->format("Y-m-d H.i.s.u");

  $rawdata = file_get_contents("php://input");
  $decoded = json_decode($rawdata);

  $logfile = "./log/{$time}.log";
  file_put_contents($logfile, print_r($decoded, true));

  $result = new stdClass();

  $start = microtime(true);
  $repo = $decoded->repository->full_name;
  $branch = str_replace('refs/heads/', '', $decoded->ref);
  $commitmsgs = [];
  foreach($decoded->commits as $commit)
    $commitmsgs[] = $commit->message;
  sort($commitmsgs);
  
  $result->repo = $repo;
  $result->branch = $branch;
  $result->commits = $commitmsgs;

  if ($exec = $targets[$repo][$branch])
  {
    $result->exec = $exec;
    exec($exec, $result);
    if ($result)
      $result->execresult = $result;
  }

  $time_elapsed_secs = round(1000 * (microtime(true) - $start));
  $result->status = "OK";
  $result->elapsed = $time_elapsed_secs;
                                                        
  echo json_encode($result);
  
  if (defined('ALERT_EMAIL'))
    mail(ALERT_EMAIL, "Deploy - $repo/$branch", json_encode($result, JSON_PRETTY_PRINT));
?>