<?php echo $class_declaration ?>  
{
  private
    $class = null,
    $state = null,
    $behaviour = null;
  
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
  public function replay() { return $this->__lime_replay(); }
  public function any($method) { return $this->__call($method, LimeMockInvocation::ANY_PARAMETERS); }
  public function reset() { return $this->state->reset(); }
  public function verify() { return $this->state->verify(); }
  public function setExpectNothing() { return $this->state->setExpectNothing(); }
  <?php endif ?>
  
  <?php echo $methods ?> 
}