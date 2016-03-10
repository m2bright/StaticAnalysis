<?php namespace Scan;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitor;
use PhpParser\ParserFactory;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeTraverser;

class Grabber implements NodeVisitor {
	private $searchingForName;
	private $foundClass=null;

	function __construct( $searchingForName="" ) {
		if($searchingForName) {
			$this->initForSearch($searchingForName);
		}
	}

	function initForSearch( $searchingForName) {
		$this->searchingForName = $searchingForName;
		$this->foundClass = null;
	}

	/**
	 * @return Class_|null
	 */
	function getFoundClass() {
		return $this->foundClass;
	}

	function beforeTraverse(array $nodes) {
		return null;
	}

	function enterNode(Node $node) {
		if ($node instanceof Class_ && strcasecmp(Util::fqn($node),$this->searchingForName)==0) {
			$this->foundClass = $node;
		}
	}

	function leaveNode(Node $node) {
		return null;
	}

	function afterTraverse(array $nodes) {
		return null;
	}

	static function getClassFromFile( $fileName, $className ) {
		static $lastFile="";
		static $lastContents;
		if($lastFile==$fileName) {
			$stmts = $lastContents;
		} else {
			$lastFile = $fileName;
			$contents = file_get_contents($fileName);
			$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
			$lastContents = $stmts = $parser->parse($contents);
		}

		if($stmts) {
			$traverser = new NodeTraverser;
			$traverser->addVisitor(new NameResolver());
			$grabber = new Grabber($className);
			$traverser->addVisitor($grabber);
			$traverser->traverse( $stmts );
			return $grabber->getFoundClass();
		}
		return null;
	}


}

?>
