<?php

/*
 * Element interface
 * @author Jake Josol
 * @description Interface for creating UI elements
 */

namespace Warp\Utils\Interfaces;

interface IElement
{	
	public function Initialize($id, $parameters);
	public function Render();
}

?>