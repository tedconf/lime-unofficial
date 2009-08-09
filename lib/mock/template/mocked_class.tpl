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
  
  public function __call($method, array $parameters)
  {
    return $this->state->invoke($this->class, $method, $parameters);
  }
  
  public function __lime_replay()
  {
    $this->state = new LimeMockReplayState($this->behaviour);
  }
  
  public function __lime_getState()
  {
    return $this->state;
  }
  
  <?php if ($generate_methods): ?>
  public function replay() { return $this->__lime_replay(); }
  public function any($method, array $parameters = array()) { return $this->state->invoke($this->class, $method); }
  public function reset() { return $this->state->reset(); }
  public function verify() { return $this->state->verify(); }
  public function setStrict() { return $this->state->setStrict(); }
  public function setFailOnVerify() { return $this->state->setFailOnVerify(); }
  public function setExpectNothing() { return $this->state->setExpectNothing(); }
  <?php endif ?>
  
  <?php echo $methods ?> 
}