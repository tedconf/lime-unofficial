<?php echo $class_declaration ?>  
{
  private $control = null;
  
  public function __construct(LimeTest $test = null)
  {
    $this->control = new LimeMockControl($test);
  }
  
  public function __call($method, array $parameters)
  {
    return $this->control->call($method, $parameters);
  }
  
  public function __lime_getControl()
  {
    return $this->control;
  }
  
  <?php if ($generate_methods): ?>
  public function replay() { return $this->control->replay(); }
  public function verify() { return $this->control->verify(); }
  public function setStrict() { return $this->control->setStrict(); }
  public function setFailOnVerify() { return $this->control->setFailOnVerify(); }
  public function setExpectNothing() { return $this->control->setExpectNothing(); }
  <?php endif ?>
  
  <?php echo $methods ?> 
}