<?php

/*
 * Migration interface
 * @author Jake Josol
 * @description Interface for creating migrations
 */

namespace Warp\Utils\Interfaces;

interface IMigration
{
	public function Up();
	public function Down();
}

?>