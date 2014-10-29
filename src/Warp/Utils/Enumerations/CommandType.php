<?php

/*
 * Command Type Enumeration 
 * @author Jake Josol
 * @description Enumeration for command types
 */

namespace Warp\Utils\Enumerations;
 
class CommandType
{
	const Add = "INSERT INTO";
	const Edit = "UPDATE";
	const Delete = "DELETE FROM";
}
 
?>