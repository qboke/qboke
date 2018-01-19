<?php
/**
 * author: Soli <soli@cbug.org>
 * date  : 2013-04-22
 * */
namespace QBoke\Site;

use \DateTime;
use QBoke\Common\QBGlobal;

class QBPost
{
	private $site;
	private $parent;
	private $name;
	private $config = array();
	private $title;
	private $date;
	private $tags;
	private $content;
	private $yfm = [ // YAML Front Matter
		['---', '---'],
		['<!--', '-->'],
	];

	public function __construct($parent, $name)
	{
		$this->parent = $parent;
		$this->name = $name;
	}

	public function load()
	{
		$this->load_config();
		return true;
	}

	public function site()
	{
		if (!isset($this->site)) {
			$this->site = $this->parent->site();
		}

		return $this->site;
	}

	public function catalog()
	{
		return $this->parent;
	}

	public function path()
	{
		$ppath = $this->parent->path();
		$path  = $ppath . '/' . $this->name;
		return rtrim($path, '/\\');
	}

	public function url_path()
	{
		$purl = $this->parent->url_path();
		$url  = $purl . '/' . $this->slug();
		return rtrim($url, '/\\');
	}

	public function url()
	{
		$site = $this->site();
		$url = $site->root() . $this->url_path() . $site->url_suffix();

		return $url;
	}

	public function slug()
	{
		if (isset($this->config) && isset($this->config['slug'])) {
			return $this->config['slug'];
		}

		return $this->name;
	}

	public function title()
	{
		if (isset($this->title)) {
			return $this->title;
		}

		if (isset($this->config) && isset($this->config['title'])) {
			$this->title = $this->config['title'];
			return $this->title;
		}

		if (preg_match('@<h1[^>]*>([^<]*)</h1>@i', $this->content(), $matches)) {
			$this->title = $matches[1];
			return $this->title;
		}

		$dot_pos = strrpos($this->name, '.');

		if ($dot_pos > 0) {
			$this->title = substr($this->name, 0, $dot_pos);
		} else {
			$this->title = $this->name;
		}

		return $this->title;
	}

	public function author()
	{
		if (isset($this->config) && isset($this->config['author'])) {
			return $this->config['author'];
		}

		return false;
	}

	public function date()
	{
		if (isset($this->date)) {
			return $this->date;
		}

		if (isset($this->config) && isset($this->config['date'])) {
			$this->date = $this->config['date'];

			if ( ! is_string( $this->date ) ) {
				$datetime = new DateTime();
				$datetime->setTimestamp( $this->date );
				$this->date = $datetime->format('Y-m-d H:i:s');
			}

			return $this->date;
		}

		$path = $this->path();
		$fstat = stat($path);
		if ($fstat === false) {
			$datetime = new DateTime();
			$this->date = $datetime->format('Y-m-d H:i:s');
			return $this->date;
		}

		$datetime = new DateTime("@{$fstat['ctime']}");
		$this->date = $datetime->format('Y-m-d H:i:s');
		return $this->date;
	}

	public function timestamp()
	{
		$datetime = new DateTime($this->date());
		return $datetime->getTimestamp();
	}

	public function tags()
	{
		if (isset($this->tags)) {
			return $this->tags;
		}

		$this->tags = array();

		if (!isset($this->config) || !isset($this->config['tags'])) {
			return $this->tags;
		}

		if (!is_array($this->config['tags'])) {
			$this->config['tags'] = explode(',', $this->config['tags']);
		}

		$this->config['tags'] = array_unique($this->config['tags']);

		foreach ($this->config['tags'] as $tag) {
			$this->tags[$tag][$this->url_path()] = $this;
		}

		return $this->tags;
	}

	public function format()
	{
		if (isset($this->config) && isset($this->config['format'])) {
			return $this->config['format'];
		}

		return 'none';
	}

	public function type()
	{
		if (isset($this->config) && isset($this->config['type'])) {
			return $this->config['type'];
		}

		return 'post';
	}

	public function excerpt()
	{
		if (isset($this->config) && isset($this->config['excerpt'])) {
			return $this->config['excerpt'];
		}

		return $this->content();
	}

	public function options($name)
	{
		if ( !isset($this->config) ) {
			return false;
		}

		$config = $this->config;

		if (isset($config['options']) && isset($config['options'][$name])) {
			return $config['options'][$name];
		}

		return false;
	}

	public function content()
	{
		$g = QBGlobal::getInstance();

		if ( isset($this->content) ) {
			return $this->content;
		}

		$err = "File Not Found!";

		$path = $this->path();
		if( !is_readable($path) ) {
			return $err;
		}

		// get rid of YAML Front Matter
		$offset = -1;
		$fh = @fopen($path, 'r');

		if (!$fh) {
			return $err;
		}

		$fline = fgets($fh);

		if ($fline !== false) {
			$left = rtrim($fline);
			$right = null;

			foreach ($this->yfm as $yfm) {
				if ($left == $yfm[0]) {
					$right = $yfm[1];
					break;
				}
			}

			if ($right !== null) {
				while (($line = fgets($fh)) !== false) {
					if (rtrim($line) === $right) {
						break;
					}
				}
			}

			$offset = ftell($fh);
		}

		fclose($fh);

		// content without YAML Front Matter
		$this->content = file_get_contents( $path, false, NULL, $offset );
		if( false === $this->content ) {
			return $err;
		}

		$parser_type = $this->format();
		$parser_cls = "\\QBoke\\Parser\\$parser_type\\$parser_type" . 'Parser';

		if (class_exists($parser_cls)) {
			$parer = new $parser_cls;
		} else {
			$parser_cls = '\QBoke\Parser\Donothing\DonothingParser';
			$parer = new $parser_cls;
		}

		$this->content = $parer->go( $this->content );

		$this->content = $g->call_hooks('qb_get_content', $this->content);
		return $this->content;
	}

	/*************************************************/

	private function load_config()
	{
		$path = $this->path();

		if( !is_readable($path) ) {
			return false;
		}

		$fh = @fopen($path, 'r');

		if (!$fh) {
			return false;
		}

		$fline = fgets($fh);

		if ($fline === false) {
			return false;
		}

		$left = rtrim($fline);
		$right = null;

		foreach ($this->yfm as $yfm) {
			if ($left == $yfm[0]) {
				$right = $yfm[1];
				break;
			}
		}

		if ($right === null) {
			return false;
		}

		$config = '';

		while (($line = fgets($fh)) !== false) {
			if (rtrim($line) === $right) {
				break;
			}

			$config .= $line;
		}

		fclose($fh);

		//$this->config = json_decode( $config, true );
		$this->config = yaml_parse( $config );

		if( is_null( $this->config ) ) {
			$this->config = array();
			return false;
		}

		return true;
	}

}

