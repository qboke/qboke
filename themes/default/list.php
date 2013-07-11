<?php
/**
 * author: Soli <soli@cbug.org>
 * date  : 2013-04-29
 * */

require_once INC_DIR . '/functions.php';

include __DIR__ . '/header.php';
?>
<!-- main start -->
<div id="main">
<?php
$pidx = 0;
while ($post = the_post() ) {
	$pidx++;
	?>
	<div id="<?php echo "post-$pidx"; ?>" class="post rcbox">
		<div class="post-header">
			<h2 class="post-title">
				<a href="<?php echo $post->url(); ?>" title="<?php echo $post->title() ?>">
					<?php echo $post->title() ?>
				</a>
			</h2>
			<em class="post-meta rcbox">
				<?php
				$dt = $post->date();
				if ( $dt ) {
					echo "On $dt ";
				}
				$author = $post->author();
				if ($author) {
					echo "By $author";
				}
				?>
			</em>
		</div>
		<div class="post-content">
			<?php
			if ( isset($fullcontent) && $fullcontent) {
				echo $post->content();
			} else {
				echo $post->excerpt() . '<span class="post-more"><a href="' . $post->url() . '">...</a></span>';
			}
			?>
		</div>
		<div class="hl"></div>
		<div class="post-footer">
			<div class="post-tags">
				<ul><?php foreach (array_keys($post->tags()) as $tag) {
					echo '<li><a href="' . $site->url() . 'tag/' . $tag . '.html">' . $tag .'</a></li>';
				}?></ul>
			</div>
			<div class="post-reply"><?php /* TODO: */ ?></div>
		</div>
	</div>
	<?php
}/*while end*/ ?>
</div>
<!-- main end -->

<?php
include __DIR__ . '/sidebar.php';
include __DIR__ . '/footer.php';
?>