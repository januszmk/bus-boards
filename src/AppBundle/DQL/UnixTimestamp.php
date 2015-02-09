<?php

namespace AppBundle\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

class UnixTimestamp extends FunctionNode {
    /*
    * holds the timestamp of the UNIX_TIMESTAMP DQL statement
    * @var mixed
    */
    protected $dateExpression;

    /**
     * @param \Doctrine\ORM\Query\SqlWalker $sqlWalker
     *
     * @return string
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'UNIX_TIMESTAMP(' .
               $sqlWalker->walkArithmeticExpression($this->dateExpression) .
               ')';
    }
    /**
     * parse - allows DQL to breakdown the DQL string into a processable structure
     * @param \Doctrine\ORM\Query\Parser $parser
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->dateExpression = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
