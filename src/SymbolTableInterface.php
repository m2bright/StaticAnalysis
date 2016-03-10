<?php namespace Scan;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;

interface SymbolTableInterface
{
	function addClass($name, Class_ $class, $file);

	function addInterface($name, Interface_ $interface, $file);

	/**
	 * @param $name string Full namespace path to a class name
	 * @return \PhpParser\Builder\Class_
	 */
	function getClass($name);

	/**
	 * @param string      $className  Full namespace path to a class name
	 * @param string      $methodName Name of the method
	 * @param ClassMethod $method     Class method
	 * @return void
	 */
	function addMethod($className, $methodName, ClassMethod $method);

	/**
	 * @return string[]
	 */
	function getAllClassNames();

	/**
	 * @param $className
	 * @param $methodName
	 * @return mixed
	 */
	function getClassMethod($className, $methodName);

	/**
	 * @param $className Full namespace path to a class
	 * @return ClassMethod[]
	 */
	function getClassMethods($className);

	/**
	 * @param $className
	 * @return string
	 */
	function getClassFile($className);

	/**
	 * @param $interfaceName
	 * @return string
	 */
	function getInterfaceFile($interfaceName);

	/**
	 * @param string $name
	 * @return bool
	 */
	function ignoreType($name);

}