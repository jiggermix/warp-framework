<?php

namespace Warp\Foundation;

class Partial
{	
	public static function Import($file)
	{
		include Resource::Path("partial").$file;
	}
}

?>