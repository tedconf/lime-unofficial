<?php echo $class_declaration ?>  
{
  private
    $class      = null,
    $state      = null,
    $behaviour  = null;
  
  public function __construct($class, LimeMockBehaviourInterface $behaviour, LimeOutputInterface $output = null)
  {
    $this->class = $class;
    $this->behaviour = $behaviour;
    $this->state = new LimeMockRecordState($this->behaviour, $output);
  }
  
  public function __call($method, $parameters)
  {
    try
    {
      return $this->state->invoke($this->class, $method, $parameters);
    }
    catch (LimeMockInvocationException $e)
    {
      // hide the internal trace to not distract when debugging test errors
      throw new LimeMockException($e->getMessage());
    }
  }
  
  public function __lime_replay()
  {
    $this->state = new LimeMockReplayState($this->behaviour);
  }
  
  public function __lime_getState()
  {
    return $this->state;
  }
  
  <?php if ($generate_controls): ?>
  public function replay() { return LimeMock::replay($this); }
  public function any($methodName) { return LimeMock::any($this, $methodName); }
  public function reset() { return LimeMock::reset($this); }
  public function verify() { return LimeMock::verify($this); }
  public function setExpectNothing() { return LimeMock::setExpectNothing($this); }
  <?php endif ?>
  
  <?php echo $methods ?> 
}