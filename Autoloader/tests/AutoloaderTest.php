<?php

use A1\Autoloader;
use org\bovigo\vfs\vfsStream;

class AutoloaderTest extends PHPUnit_Framework_TestCase{

	private $tree = [
		'folder1'=>[
			'file1.php' => 'file1',
			'file2.html' => 'file2',
			'file3.js' => 'file3',
			'subfolder1' =>[
				'subfile1.php' => 'file1',
				'subfile2.php' => 'file2',
				'subfile3.txt' => 'file3',
			]
		]
	];

	public function __construct(){
		$vfs = vfsStream::setup('root');
		vfsStream::create($this->tree, $vfs);
	}

	public function testExtensions(){

		$autoloader = new Autoloader();
		
		$autoloader->allow('html','js','php');
		$this->assertEquals($autoloader->extensions(),['php','html','js']);

		$autoloader->disallow('js','html');
		$this->assertEquals($autoloader->extensions(),['php']);
	}

	public function testBasicRegistrationPath(){

		$dir1 = vfsStream::url('root/folder1/subfolder1');
		$autoloader = new Autoloader();
		$autoloader->allow('php');

		$autoloader->register($dir1);
		$this->assertEquals($autoloader->classes(),[
			'subfile1' => 'vfs://root/folder1/subfolder1/subfile1.php',
			'subfile2' => 'vfs://root/folder1/subfolder1/subfile2.php'
		]);

		$dir2 = vfsStream::url('root/folder1');
		$autoloader->register($dir2);
		$this->assertEquals($autoloader->classes(),[
			'file1' => 	  'vfs://root/folder1/file1.php',
			'subfile1' => 'vfs://root/folder1/subfolder1/subfile1.php',
			'subfile2' => 'vfs://root/folder1/subfolder1/subfile2.php'
		]);

		
	}

	public function testNamespacedRegistrationPath(){
		$namespace = 'MyNamespace';
		$dir = vfsStream::url('root/folder1/subfolder1');
		
		$autoloader = new Autoloader();
		$autoloader->register([$namespace => $dir]);
		
		$this->assertEquals($autoloader->classes(),[
			'MyNamespace\\subfile1' => 'vfs://root/folder1/subfolder1/subfile1.php',
			'MyNamespace\\subfile2' => 'vfs://root/folder1/subfolder1/subfile2.php'
		]);
	}

}
