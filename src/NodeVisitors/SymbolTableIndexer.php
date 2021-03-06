<?php namespace Scan\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitor;
use Scan\Util;

class SymbolTableIndexer implements NodeVisitor {
	private $index;
	private $classStack = [];
	private $filename = "";

	function __construct( $index ) {
		$this->index=$index;
	}
	function beforeTraverse(array $nodes) {
		return null;
	}

	function setFilename($filename) {
		$this->filename=$filename;
	}

	function enterNode(Node $node) {
		switch(get_class($node)) {
			case Class_::class:
				$name=$node->namespacedName->toString();
				$file=$this->index->getClassFile($name);
				if($file) {
					echo $this->filename." ".$node->getLine().": Class $name already exists in $file... Ignoring\n";
				} else {
					$this->index->addClass($name, $node, $this->filename);
				}
				array_push($this->classStack, $node);
				break;
			case Interface_::class:
				$name=$node->namespacedName->toString();
				$this->index->addInterface($name, $node, $this->filename);
				array_push($this->classStack, $node);
				break;
			case Function_::class:
				$name=$node->namespacedName->toString();
				$this->index->addFunction($name, $node, $this->filename);
				break;
			case Trait_::class:
				$name=$node->namespacedName->toString();
				$this->index->addTrait($name, $node, $this->filename);
				array_push($this->classStack, $node);
				break;
		}
		if($node instanceof ClassMethod && count($this->classStack)>0) {
			$classNode=$this->classStack[count($this->classStack)-1];
			$className=$classNode->namespacedName->toString();
			$this->index->addMethod($className, $node->name, $node);
		}
		return null;
	}

	function leaveNode(Node $node) {
		if($node instanceof Class_ || $node instanceof Interface_ || $node instanceof Trait_) {
			array_pop($this->classStack);
		}
		return null;
	}

	function afterTraverse(array $nodes) { 
		return null;
	}
}

?>
