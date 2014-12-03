<?php

namespace Warp\Foundation;

class Partial implements IElement
{	
	public static function Import($file)
	{
		include Resource::Path("partials").$file;
	}
}

?>