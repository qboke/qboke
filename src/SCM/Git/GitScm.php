<?php
/**
 * author: Soli <soli@cbug.org>
 * date  : 2013-06-04
 */
namespace QBoke\SCM\Git;

use QBoke\SCM\QBScm;
use CzProject\GitPhp\Git;

class GitScm extends QBScm
{
	private $cli  = null;
	private $repo = null;

	public function __construct()
	{
	}

	public function init($path, $opts)
	{
		$pkey = null;
		if (array_key_exists('pkey', $opts) && strlen($opts['pkey']) > 0) {
			$pkey = $opts['pkey'];
			if (substr($pkey, 0, 1) != '/') {
				$pkey = ABSPATH . "/$pkey";
			}

			$pkey = realpath($pkey);
		}

		try {
			$this->cli = new Git();

			if ($pkey != null) {
				$this->cli->addConfig('core.sshCommand', "ssh -i '$pkey' -o IdentitiesOnly=yes");
			}

			if (file_exists($path . '/.git/HEAD')) {
				$this->repo = $this->cli->open($path);
			} else {

				$this->repo = $this->cli->cloneRepository($opts['remote'], $path);
				$this->repo->checkout($opts['branch']);
			}
			return true;
		} catch (\Exception $e) {
			trigger_error( print_r($e), E_USER_ERROR );
		}

		return false;
	}

	public function pull()
	{
		if ($this->repo === null) {
			return false;
		}

		try {
			$this->repo->pull();
			return true;
		} catch (\Exception $e) {
			trigger_error( print_r($e), E_USER_ERROR );
		}
		return false;
	}

	public function add($param)
	{
		if ($this->repo === null) {
			return false;
		}

		try {
			if ($param === true) {
				$this->repo->addAllChanges();
				return true;
			}

			$this->repo->addFile($param);
			return true;
		} catch (\Exception $e) {
			trigger_error( print_r($e), E_USER_ERROR );
		}
		return false;
	}

	public function del($param)
	{
		if ($this->repo === null) {
			return false;
		}

		try {
			$this->repo->removeFile($param);
			return true;
		} catch (\Exception $e) {
			trigger_error( print_r($e), E_USER_ERROR );
		}
		return false;
	}

	public function push()
	{
		if ($this->repo === null) {
			return false;
		}
		try {
			$this->repo->push();
			return true;
		} catch (\Exception $e) {
			trigger_error( print_r($e), E_USER_ERROR );
		}
		return false;
	}
}

