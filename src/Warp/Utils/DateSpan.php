<?php

/**
 * DateSpan class
 * @author Jake Josol
 * @description Utility class for all date spans
 */

namespace Warp\Utils;

class DateSpan 
{
	protected $start, $end;

	public function __construct($start, $end)
	{
		$this->start = strtotime($start);
		$this->end = strtotime($end);
	}

	public function ToMinutes()
	{
		return (int) (($this->end - $this->start)/60);
	}

	public function ToString()
	{
		$minutes = $this->ToMinutes();

		if($minutes >= 60 * 24 * 365)
		{ 
			if((int)($minutes/60/24/365) > 1)
				return ((int)($minutes/60/24/365)) . " years ago";
			else
				return "a year ago";
		}
		else if($minutes >= 60 * 24 * 30)
		{
			if((int)($minutes/60/24/30) > 1)
				return ((int)($minutes/60/24/30)) . " months ago";
			else
				return "a month ago";
		}
		else if($minutes >= 60 * 24 * 7)
		{
			if((int)($minutes/60/24/7) > 1)
				return ((int)($minutes/60/24/7)) . " weeks ago";
			else
				return "a week ago";
		}
		else if($minutes >= 60 * 24)
		{
			if((int)($minutes/60/24) > 1)
				return ((int)($minutes/60/24)) . " days ago";
			else
				return "a day ago";
		}
		else if($minutes >= 60)
		{
			if((int)($minutes/60) > 1)
				return ((int)($minutes/60)) . " hours ago";
			else
				return "an hour ago";
		}
		else
		{
			return "a few minutes ago";
		}
	}
}

?>