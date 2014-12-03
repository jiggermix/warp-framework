<?php

namespace Warp\Foundation;

use Warp\Core\Resource;

class Partial
{	
	public static function Import($file)
	{
		include Resource::Path("partial") . $file;
	}
}

?>