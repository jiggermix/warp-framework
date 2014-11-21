<?php

/**
 * Morpheus Templating Engine
 * @author Jake Josol
 * @description Morpheus templating engine
 */

namespace Warp\Templating;

use Warp\Utils\FileHandle;

class Morpheus
{
	const FILE_EXTENSION = ".morph.php";
	const XML_HEADER = "<?xml version='1.0' encoding='UTF-8'?>";
	protected $directory;
	protected $layout;
	protected $page;
	protected $fragment;
	protected $data;

	protected function __construct() {}

	protected function getLayout()
	{
		return $this->layout;
	}
	
	protected function getPage()
	{
		return $this->page;
	}

	protected function getFragment()
	{
		return $this->fragment;
	}

	protected function getData()
	{
		return $this->$data;
	}

	public function Layout($layout)
	{
		static::$layout = $layout;

		return $this;
	}

	public function Page($page)
	{
		static::$page = $page;

		return $this;
	}

	public function Fragment($fragment)
	{
		$this->fragment = $fragment;

		return $this;
	}

	public function Data($data)
	{
		$this->data = $data;

		return $this;
	}

	public static function Accepts($file)
	{
		return stripos($file, self::FILE_EXTENSION) > 0;
	}

	public static function Make()
	{
		$class = get_called_class();
		$template = new $class;

		return $template;
	}

	public function Compile()
	{
		$layout = static::getLayout();
		$page = static::getPage();
		$fragment = $this->getFragment();
		$data = $this->getData();		

		$layoutFile = new FileHandle($layout);
		$xmlText = self::XML_HEADER . $file->Contents();
		$file->Close();

		return $xmlText;
	}
}

?>