<?php

/**
 * Job class
 * @author Jake Josol
 * @description Utility class for all jobs
 */

namespace Warp\Process;

use DateTime;
use Warp\Core\Reference;
use Warp\Data\Schema;
use Warp\Foundation\Model;
use Warp\Http\Response;
use Warp\Utils\Interfaces\IJob;
use Warp\Utils\Enumerations\SystemField;
use Warp\Utils\FileHandle;

class Job
{
	protected $instance;

	public function __construct($handler, $runAt=0, $priority=0)
	{
		// Check handler
		if($handler instanceof JobModel)
		{
			$this->instance = $handler;
			return;
		}

		// Convert handler into text object
		$serializedHandler = base64_encode(serialize($handler));

		// Check runAt
		if(is_int($runAt))
		{
			$now = new DateTime(date("Y-m-d H:i:s"));
			$now->modify("+{$runAt} minutes");
			$runAt = $now->format("Y-m-d H:i:s");
		}

		$model = new JobModel;
		$model->handler = $serializedHandler;
		$model->runAt = $runAt;
		$model->attempts = 0;
		$model->priority = $priority;

		$this->instance = $model;
		$this->instance->Save();
	}

	public static function Enqueue($handler, $runAt=0, $priority=0)
	{
		return new Job($handler, $runAt, $priority);
	}

	public function Lock()
	{
		$this->instance->attempts += 1;
		$this->instance->lockedAt = date("Y-m-d H:i:s");
		$this->instance->Save();
	}

	public function Release()
	{
		$this->instance->lockedAt = null;
		$this->instance->Save();	
	}

	public function Fail($message)
	{
		$this->instance->failedAt = date("Y-m-d H:i:s");
		$this->instance->lastError = $message;
		$this->instance->Save();
	}

	public function Run()
	{
		$handler = $this->instance->handler;
		$deserializedHandler = unserialize(base64_decode($handler));
		
		// Lock the run
		$this->Lock();

		// Run the job
		try
		{
			// Check implementation
			if(!($deserializedHandler instanceof IJob)) throw new \Exception("The specified job does not implement IJob");

			// Run the handler
			$deserializedHandler->Perform();

			// Release the run
			$this->Release();
			$this->instance->Delete();
		}
		catch(\Exception $ex)
		{
			// Catch any errors
			$this->Fail($ex->getMessage());
		}
	}

	public static function Load()
	{
		$query = JobModel::Query()->WhereLessThanOrEqualTo("runAt", date("Y-m-d H:i:s"));
		$pendingJob = $query->First();

		if(!$pendingJob) return;

		$model = new JobModel($pendingJob[JobModel::Key()]);
		$model->Fetch();

		$job = new Job($model);
		$job->Run();
	}

	public static function Install()
	{
		// To-do: install to cron jobs
	}
}

class JobModel extends Model
{
	protected static $source = "_job";
	protected static $key = "id";
	protected static $fields = array();

	protected static function build()
	{
		self::Has(SystemField::ID)->Increment();
		self::Has("handler")->Text();
		self::Has("runAt")->DateTime();
		self::Has("priority")->Integer();
		self::Has("attempts")->Integer();
		self::Has("lastError")->Text();
		self::Has("lockedAt")->DateTime();
		self::Has("failedAt")->DateTime();
		self::Has("queue");
	}
}