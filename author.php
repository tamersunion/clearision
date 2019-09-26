<?php get_header(); ?>

<?php
if(isset($_GET['author_name'])) {
    $curauth = get_userdatabylogin($author_name);
} else {
    $curauth = get_userdata(intval($author));
}
$author_comment_count = $wpdb->get_var('SELECT COUNT(1) FROM ' . $wpdb->comments . ' WHERE comment_approved=1 and user_id=' . $curauth->ID);
?>


<div class="page-warp">
<div class="content">
	<hgroup class="post_header" style="text-align: center;">
	    <img style="width: 6em!important; height: 6em!important;max-width: 100%!important; max-height: unset;border-radius: 10px;" src="<?php echo get_avatar_data($curauth)['url']; ?>"><br />
		<h2 class="post_title"><?php echo $curauth->display_name; ?></h2>
		<?php if($curauth->job){ ?><p><?php echo $curauth->job; ?></p><?php } ?>
        <?php if($curauth->description){ ?><p><?php echo $curauth->description; ?></p><?php } ?>
		<div style="margin: 10px 0;" class="counts">文章<span style="margin-left: 8px;padding-right: 20px;margin-right: 20px;border-right: 1px solid #eee;"><?php the_author_posts(); ?></span>评论<span style="margin-left: 8px;    border-right: none;margin-right: 0;padding-right: 0;"><?php echo $author_comment_count ?></span></div>
	</hgroup>
</div>
</div>




<?php if (have_posts()) : ?>
	<?php while (have_posts()) : the_post(); ?>
	<div class="page-warp">
		<div class="content">
			<article <?php post_class(); ?>>

				<?php get_template_part('content'); ?>

			</article>
		</div>
	</div>
	<?php endwhile; ?>

	<div id="page_nav">
		<?php clrs_pagenavi(); ?>
	</div>

	<?php  else : ?>
    <div class="page-warp">
    	<div class="content">
            <article class="post-index-header post type-page status-publish hentry">
    			<hgroup class="post_header" style="padding-bottom: .8em;">
                	<h2 class="post_title">找不到内容</h2>
                </hgroup>
                <div class="post_content">
                    <p>该作者没有发表任何文章。</p>
                </div>
    		</article>
    	</div>
    </div>
<?php endif; ?>
<?php $ldis = get_option('clrs_link_display');
$c_ldis = $ldis !== "yes" ? 'style="display: none"' : ''; ?>
<div id="link" <?php echo $c_ldis; ?>>
	<div id="link_content">
		<h3 id="link-head">
			<?php __('友情链接', 'clrs'); ?>
		</h3>
		<?php echo get_option('clrs_link'); ?>
	</div>
</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>