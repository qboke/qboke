<?php
/**
 * author: Soli <soli@cbug.org>
 * date  : 2018-01-20
 * */
namespace QBoke\Plugin\HeaderFooter;

use QBoke\Common\QBGlobal;

class HeaderFooterPlugin
{
	public function init()
	{
		$g = QBGlobal::getInstance();

		$g->add_hook('qb_header', array(&$this, 'header'));
		$g->add_hook('qb_footer', array(&$this, 'footer'));
	}

	public function header()
	{
		$g = QBGlobal::getInstance();

		if (!$g->response->is_post()) {
			return;
		}

		$posts = $g->response->posts();
		$post = current($posts);

		if ( $post === false) {
			return;
		}

		$header = $post->options('header');

		if ($header === false) {
			return;
		}

		echo $header;
	}

	public function footer()
	{
		$g = QBGlobal::getInstance();

		if (!$g->response->is_post()) {
			return;
		}

		$posts = $g->response->posts();
		$post = current($posts);

		if ( $post === false) {
			return;
		}

		$footer = $post->options('footer');

		if ($footer === false) {
			return;
		}

		echo $footer;
	}
}
