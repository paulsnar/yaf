<?php declare(strict_types=1);
namespace PN\Yaf\Runtime;

class ShutdownJobRunner
{
  private $jobs = [ ];

  public function enqueue(\Closure $job)
  {
    $this->jobs[] = $job;
  }

  public function run()
  {
    foreach ($this->jobs as $job) {
      $job();
    }
  }
}
