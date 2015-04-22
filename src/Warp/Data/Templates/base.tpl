<?php

/*
 * Migration File
 * @description A file for migrating database objects
 */

use Warp\Utils\Interfaces\IMigration;

class {{class}} implements IMigration
{
	/*
	 * Commit the migration
	 */
	public function Up()
	{
		Schema::Table("{{table}}")
			->ID()
			->Timestamps()
			->Create();
	}

	/*
	 * Revert the migration
	 */
	public function Down()
	{
		Schema::Table("{{table}}")->Drop();
	}
}