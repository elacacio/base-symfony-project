<?php

namespace Arcmedia\CmsBundle\Twig;

use Twig_Token;
use Twig_TokenParser;

/**
 * Class ScriptDeclarationTokenParser
 * @package Arcmedia\CmsBundle\Twig
 */
class ScriptDeclarationTokenParser extends Twig_TokenParser
{
    /**
     * {@inheritdoc}
     */
    public function parse(Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse([$this, 'testEndTag'], true);

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $nodes = ['body' => $body];

        return new ScriptDeclarationNode($nodes, [], $token->getLine(), $this->getTag());
    }

    /**
     * {@inheritdoc}
     */
    public function testEndTag(Twig_Token $token)
    {
        return $token->test(['end'.$this->getTag()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return 'script_declaration';
    }
}
