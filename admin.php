<?php
global $bs_options;
if (empty($bs_options)) {
	$bs_options = get_option('bs_options');
}

if (!empty($_POST['bs_save'])) {
	check_admin_referer('blog-stats');
	
	$bs_options['feedburner_id']	   = trim($_POST['feedburner_id']);
	$bs_options['feedburner_location'] = trim($_POST['feedburner_location']);
	
	update_option('bs_options', $bs_options);
	
	echo '<div id="message" class="updated fade"><p>' . __('Settings saved successfully.', 'blog_stats') . '</p></div>' . "\n";		
} else if (!empty($_POST['bs_recalculate'])) {
	bs_calculate_daily_stats();
	$bs_options = get_option('bs_options');
	
	echo '<div id="message" class="updated fade"><p>' . __('Stats recalculated successfully.', 'blog_stats') . '</p></div>' . "\n";		
}
?>
<div class="wrap">	
<br style="clear:both" />
<div style="width:180px;float:right;background-color: #fffeeb;border: 1px solid #ccc;margin-top:10px;padding:5px;">
<h3>About this Plugin:</h3>
<ul>
<li><a href="http://www.improvingtheweb.com/wordpress-plugins/blog-stats/">Plugin homepage</a></li>
<li><a href="http://www.improvingtheweb.com/wordpress-plugins/blog-stats/#comments">Suggest a Feature</a></li>
<li><a href="http://www.improvingtheweb.com/wordpress-plugins/blog-stats/#comments">Report a bug</a></li>
<li><a href="http://www.improvingtheweb.com/donate/">Donate with PayPal</a></li>
</ul>
<h3>About the author:</h3>
<ul>
<li><a href="http://www.improvingtheweb.com/">Visit my blog</a></li>
<li><a href="http://twitter.com/improvingtheweb">Follow me on twitter</a></ii>
<li><a href="http://www.improvingtheweb.com/feed/">Subscribe to my RSS feed</a></li>
</ul>
<h3>Acknowledgements:</h3>
<p>Many thanks to <a href="http://www.problogdesign.com">ProBlogDesign</a> and <a href="http://www.wprecipes.com">WPRecipes</a> for the idea.</p>
</div>						
<div style="float:left;">
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<h2><?php _e('Blog Stats Options', 'blog_stats'); ?></h2>
<table class="form-table" style="width:350px;">
<tr>
<th scope="row"><?php _e('Feedburner ID', 'blog_stats'); ?></th>
<td><input size="15" type="text" name="feedburner_id" value="<?php echo htmlspecialchars($bs_options['feedburner_id']); ?>" /></td>
</tr>
<tr>
<th scope="row"><?php _e('Location', 'blog_stats'); ?></th>
<td>
<select name="feedburner_location">
<option value="old" <?php if ($bs_options['feedburner_location'] == 'old'): ?>selected="selected"<?php endif; ?>>Feedburner.com</option>
<option value="new" <?php if ($bs_options['feedburner_location'] == 'new'): ?>selected="selected"<?php endif; ?>>Feedproxy.google.com</option>
</select>
</td>
</tr>
<tr>
<td colspan="2">
<?php wp_nonce_field('blog-stats'); ?>
<span class="submit"><input name="bs_save" value="<?php _e('Save Changes'); ?>" type="submit" /></span>
</td>
</tr>	
</table>	
</form>
<h2><?php _e('Current Blog Stats', 'blog_stats'); ?></h2>
<table class="form-table" style="width:350px">
<?php if (empty($bs_options['after_install'])): ?>
<tr>
<th><strong>Short code</strong></th>
<th><strong>Value</strong></th>
</tr>
<tr>
<td>[user_count]</td>
<td><?php echo bs_user_count(); ?></td>
</tr>
<tr>
<td>[post_count]</td>
<td><?php echo bs_post_count(); ?></td>
</tr>
<tr>
<td>[page_count]</td>
<td><?php echo bs_page_count(); ?></td>
</tr>
<tr>
<td>[comment_count]</td>
<td><?php echo bs_comment_count(); ?></td>
</tr>
<tr>
<td>[trackback_count]</td>
<td><?php echo bs_trackback_count(); ?></td>
</tr>
<tr>
<td>[avg_comments_per_post]</td>
<td><?php echo bs_avg_comments_per_post(); ?></td>
</tr>		
<tr>
<td>[category_count]</td>
<td><?php echo bs_category_count(); ?></td>
</tr>
<tr>
<td>[tag_count]</td>
<td><?php echo bs_tag_count(); ?></td>
</tr>
<tr>
<td>[link_count]</td>
<td><?php echo bs_link_count(); ?></td>
</tr>
<tr>
<td>[pagerank]</td>
<td><?php echo bs_pagerank(); ?></td>
</tr>	
<tr>
<td>[technorati_authority]</td>
<td><?php echo bs_technorati_authority(); ?></td>
</tr>
<tr>
<td>[technorati_rank]</td>
<td><?php echo bs_technorati_rank(); ?></td>
</tr>	
<tr>
<td>[alexa_rank]</td>
<td><?php echo bs_alexa_rank(); ?></td>
</tr>			
<tr>
<td>[feedburner_subscribers]</td>
<td><?php echo bs_feedburner_subscribers(); ?></td>
</tr>
<tr>
<td>[google_backlinks]</td>
<td><?php echo bs_google_backlinks(); ?></td>
</tr>
<tr>
<td>[yahoo_backlinks]</td>
<td><?php echo bs_yahoo_backlinks(); ?></td>
</tr>
<tr>
<td>[delicious_bookmarks]</td>
<td><?php echo bs_delicious_bookmarks(); ?></td>
</tr>	
<?php else: ?>
<tr>
<td colspan="2"><?php _e('You have just installed this plugin. Please click the button below to calculate the initial values. From then on, the stats will be updated automatically every day.', 'blog_stats'); ?></td>
</tr>
<?php endif; ?>
<tr>
<td colspan="2">
<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<?php wp_nonce_field('blog-stats'); ?>
<span class="submit"><input name="bs_recalculate" value="<?php if (empty($bs_options['after_install'])): ?><?php _e('Recalculate', 'blog_stats'); ?><?php else: ?><?php _e('Calculate', 'blog_stats'); ?><?php endif; ?>" type="submit" /></span>
</form>	
</td>
</tr>
<tr>
<th colspan="2">
Made by <a href="http://www.improvingtheweb.com/">Improving The Web</a> | <a href="http://www.improvingtheweb.com/feed/">RSS</a> | <a href="http://twitter.com/improvingtheweb">Twitter</a>
</th>
</tr>
</table>	
</div>
</div>
<br style="clear:both" />