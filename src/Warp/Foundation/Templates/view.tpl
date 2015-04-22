<?php

/*
 * {{class}}
 * @author Jake Josol
 * @description {{class}}
 */
 
 class {{class}} extends View
 {
	 public function Render()
	 {
		return View::Make()->Layout("{{layout}}")->Page("{{page}}")->Render();
	 }
 }