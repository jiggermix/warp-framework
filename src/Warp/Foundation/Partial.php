<?php

namespace Warp\Foundation;

use Warp\Core\Reference;

class Partial
{	
	public static function Import($file)
	{
		include Reference::Path("partial") . $file;
	}
}

?>