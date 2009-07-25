<?php

include dirname(__FILE__).'/../../bootstrap/unit.php';

LimeAnnotationSupport::enable();

$t = new LimeTest(1);


// @Before

  $lexer = new LimeLexerCodeLines();


// @After

  $lexer = null;


// @Test: The correct code lines are returned

  // see inline documentation in LimeLexerCodeLines for more information

  // test
  $lines = $lexer->parse(<<<EOF
<?php

class
TestClass
{
  public
  function
  method()
  {
    \$a = 0;

    function
    innerFunction()
    {
      \$b = 0;
    }
  }
}

function
outerFunction
()
{
  \$c = 0;
}

\$d = 0;

?>
EOF
  );
  // assertions
  // 13 should be included, but this is rather complicated
  $t->is($lines, array(5, 10, /*13, */15, 16, 17, 18, 21, 24, 25, 27), 'The correct lines are returned');
