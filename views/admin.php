<![CDATA[ TentBlogger SEO Sitemap 1.0]]>
<!-- This plugin is not multisite compatible. -->
<?php $options = get_option('tentblogger-seo-sitemap'); ?>
<div class="wrap">
  <div class="icon">
    <h2>
      <?php _e("TentBlogger's SEO Sitemap Plugin", "tentblogger-seo-sitemap"); ?>
    </h2>
  </div>
	<?php if(tentblogger_is_private_blog()) { ?>
		<div class="error">
			<p>
				<?php _e('Your blog is blocking search engines. Update <a href="options-privacy.php">your privacy settings</a>!', "tentblogger-seo-sitemap"); ?>
			</p>
		</div>
	<?php } else { ?>
  <div class="postbox-container">
    <div id="poststuff" class="postbox">
      <h3 class="hndle">
        <span>
          <?php _e("SEO Sitemap", "tentblogger-seo-sitemap"); ?>
        </span>
      </h3>
      <div class="inside">
        <p class="description">
          <?php _e('This plugin attempts to streamline the sitemap generation process. Automatic create, gzipping, submission to search engines, and daily execution.', 'tentblogger-seo-sitemap') ?>
        </p>
        <fieldset>
          <span id="last-executed">
            <strong>
              <?php _e('Last Successfully Executed: ', 'tentblogger-seo-sitemap'); ?>
            </strong>
          </span>
          <span id="build-date">
            <?php echo $options['tentblogger-sitemap-date'] ? $options['tentblogger-sitemap-date'] : __("Never", 'tentblogger-seo-sitemap');; ?>
          </span>
          <span id="error-message">
            <?php _e('Your server is not configure to support file-write access. Please see <a href="http://tentblogger.com/seo-sitemap/" target="_blank">this post</a> for troubleshooting instructions.', 'tentblogger-seo-sitemap'); ?>
          </span>
          <ul id="tentblogger-seo-sitemap-information" class="tentblogger-seo-sitemap-option">
            <li>
              <?php _e('Sitemap successfully generated', 'tentblogger-seo-sitemap'); ?>
            </li>
          </ul>
          <ul id="tentblogger-seo-sitemap-information" class="tentblogger-seo-sitemap-option">
            <li>
              <?php _e('Sitemap submitted to Google, Bing, and Yahoo!', 'tentblogger-seo-sitemap'); ?>
            </li>
          </ul>
          <ul id="tentblogger-seo-sitemap-information" class="tentblogger-seo-sitemap-option">
            <li>
              <?php _e('Sitemap successfully gzipped.', 'tentblogger-seo-sitemap'); ?>
            </li>
          </ul>
        </fieldset>
        <input type="submit" id="create_sitemap" name="create_sitemap" class="button-primary" value="Build Sitemap Now" />
        <span id="sitemap_url">	
          <?php echo get_bloginfo('url') . '/sitemap.xml'; ?>
        </span>
      </div>
        <div class="inside">
          <p>
            <?php 
              _e("Read more about the plugin <a href=\"http://tentblogger.com/seo-sitemap\">here</a> and check out <a href=\"http://profiles.wordpress.org/users/tentblogger/\">my other plugins</a>! Feel free to <a href=\"http://twitter.com/tentblogger\" target=\"_blank\">follow me</a> on Twitter!", 'tentblogger-seo-sitemap');
            ?>
          </p>
        </fieldset>
      </div>
    </div>
	<?php } // end if ?>
</div>
</div>