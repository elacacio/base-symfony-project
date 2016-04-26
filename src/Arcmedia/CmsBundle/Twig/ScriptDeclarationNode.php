<?php

namespace Arcmedia\CmsBundle\Twig;

use Twig_Compiler;
use Twig_Node;

/**
 * Class ScriptDeclarationNode
 * @package Arcmedia\CmsBundle\Twig
 */
class ScriptDeclarationNode extends Twig_Node
{

    /**
     * @param \Twig_Compiler $compiler
     */
    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this)
            ->write('$declarations = $this->env->getGlobals()["script_declarations"];')
            ->raw("\n")
            ->write('ob_start();')
            ->raw("\n");

        $body = $this->getNode('body');
        $count = $body->count();
        if ($count > 0) {
            /** @var Twig_Node $firstNode */
            $firstNode = $body->nodes[0];
            $firstData = $firstNode->getAttribute('data');
            $firstData = preg_replace('/<script.*?>/', '', $firstData);
            $firstNode->setAttribute('data', $firstData);
            /** @var Twig_Node $lastNode */
            $lastNode = $body->nodes[$count - 1];
            $lastData = $lastNode->getAttribute('data');
            $lastData = str_replace('</script>', '', $lastData);
            $lastNode->setAttribute('data', $lastData);
        } else {
            $data = $body->getAttribute('data');
            $dom = FluentDOM($data);
            $data = (string) $dom->find('//script')->text();
            $body->setAttribute('data', $data);
        }

        $compiler->subcompile($body);
        $compiler->write('$body = ob_get_clean();')
            ->raw("\n")
            ->write('$declarations->append($body);')
            ->raw("\n");
    }
}