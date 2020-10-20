<?php
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

  sleep(2);
  
  $time_elapsed_secs = round(1000 * (microtime(true) - $start));
  $result->repo = $repo;
  $result->branch = $branch;
  $result->status = "OK";
  $result->commits = $commitmsgs;
  $result->elapsed = $time_elapsed_secs;
  
  echo json_encode($result);
?>