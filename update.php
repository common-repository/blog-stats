<?php
set_time_limit(0);
class BlogStatsUpdater {
	var $snoopy   = false;
	var $blog_url = '';
	var $options  = array();
	
	function BlogStatsUpdater() {
		if (!class_exists('Snoopy')) {
			require_once ABSPATH . WPINC . '/class-snoopy.php';
		}
		
		$this->snoopy   = new Snoopy();
		$this->blog_url = get_bloginfo('url');
		$this->options  = get_option('bs_options');
	}
	
	function execute() {
		$this->options['user_count']		     = $this->user_count();
		$this->options['post_count'] 			 = $this->post_count();
		$this->options['page_count']			 = $this->page_count();
		$this->options['comment_count'] 		 = $this->comment_count();
		$this->options['trackback_count'] 	     = $this->trackback_count();
		$this->options['avg_comments_per_post']  = $this->avg_comments_per_post();
		$this->options['category_count'] 	     = $this->category_count();
		$this->options['tag_count']				 = $this->tag_count();
		$this->options['link_count']			 = $this->link_count();
		$this->options['pagerank']			     = $this->pagerank();	
		$this->options['alexa_rank']    		 = $this->alexa_rank();
		$this->options['feedburner_subscribers'] = $this->feedburner_subscribers();
		$this->options['google_backlinks'] 	     = $this->google_backlinks();
		$this->options['yahoo_backlinks'] 	 	 = $this->yahoo_backlinks();
		$this->options['delicious_bookmarks']    = $this->delicious_bookmarks();
		$technorati 							 = $this->technorati();
		$this->options['technorati_authority'] 	 = $technorati['authority'];
		$this->options['technorati_rank']	     = $technorati['rank'];
		
		unset($this->options['after_install']);
		
		return $this->options;	
	}
	
	function user_count() {
		global $wpdb;
		
		return (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->users);
	}
	
	function post_count() {
		global $wpdb;
		
		return (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->posts . ' WHERE post_status = "publish" AND post_type = "post"');
	}
	
	function page_count() {
		global $wpdb;
		
		return (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->posts . ' WHERE post_status = "publish" AND post_type = "page"');
	}
	
	function comment_count() {
		global $wpdb;
		
		return (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->comments . ' WHERE comment_approved = "1"');
	}
	
	function trackback_count() {
		global $wpdb;
		
		return (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->comments . ' WHERE comment_type = "pingback"');
	}
	
	function avg_comments_per_post() {
		$comment_count = $this->comment_count();
		$post_count    = $this->post_count();
		
		if ($post_count) {
			return round($comment_count/$post_count);
		} else {
			return 0;
		}
	}
	
	function category_count() {		
		return count(get_all_category_ids());
	}
	
	function tag_count() {
		global $wpdb;
		
		return (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->terms . ' INNER JOIN ' . $wpdb->term_taxonomy . ' ON ' . $wpdb->terms . '.term_id = ' . $wpdb->term_taxonomy . '.term_id WHERE ' . $wpdb->term_taxonomy . '.taxonomy = "post_tag"');
	}
	
	function link_count() {
		global $wpdb;
		
		return (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->links . ' WHERE link_visible = "Y"');
	}
			
	function pagerank() {		
		$url = str_replace('http://', '', $this->blog_url);
				
		$ch = _bs_get_ch($url);
		
		$old_user_agent = $this->snoopy->agent;
		
		$this->snoopy->agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.6) Gecko/20060728 Firefox/1.5';
		
		if ($this->snoopy->fetch('http://toolbarqueries.google.com/search?client=navclient-auto&ch=' . $ch . '&features=Rank&q=info:' . $url . '&num=100&filter=0')) {
			$pos = strpos($this->snoopy->results, 'Rank_');
			if ($post !== false) {
				$pr = substr($this->snoopy->results, $pos + 9);
				$pr = trim($pr);
				$pr = str_replace("\n", '', $pr);
				return $pr;
			}
		}
	
		return -1;
	}
			
	function technorati() {
		$result = array('rank' => -1, 'authority' => -1);
		
		if ($this->snoopy->fetch('http://www.technorati.com/blogs/' . urlencode(str_replace(array('http://', 'www.'), '', $this->blog_url)) . '?reactions')) {			
			if (preg_match('/<div class="rank">Rank: ([0-9\,]+)/si', $this->snoopy->results, $match)) {
				$result['rank'] = (int) str_replace(',', '', $match[1]);
			} 
			if (preg_match('/<div><a[^>]*>Authority: ([0-9,]+)<\/a>/si', $this->snoopy->results, $match)) {
				$result['authority'] = (int) str_replace(',', '', $match[1]);
			}
		}
				
		return $result;
	}	

	function alexa_rank() {
		if ($this->snoopy->fetch('http://data.alexa.com/data?cli=10&dat=snbamz&url=' . urlencode($this->blog_url))) {
			if (preg_match('/<POPULARITY URL="[^"]*" TEXT="([0-9]+)"/si', $this->snoopy->results, $match)) {
				return (int) $match[1];
			}			
		}
		
		return -1;
	}

	function feedburner_subscribers() {		
		if (empty($this->options['feedburner_id'])) {
			return -1;
		}

		if ($this->options['feedburner_location'] == 'old') {
			if ($this->snoopy->fetch('http://api.feedburner.com/awareness/1.0/GetFeedData?uri=' . $this->options['feedburner_id'])) {
				if (preg_match('/circulation="([0-9]+)"/i', $this->snoopy->results, $match)) {
					return (int) $match[1];
				}
			}
		} else if (function_exists('is_executable') && is_executable('/usr/local/bin/curl')) {
			if ($this->snoopy->fetch('https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=' . $this->options['feedburner_id'])) {
				if (preg_match('/circulation="([0-9]+)"/i', $this->snoopy->results, $match)) {
					return (int) $match[1];
				}
			}
		} else if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, 'https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=' . $this->options['feedburner_id']);
			$result = curl_exec($ch);
			curl_close($ch);

			if ($result) {
				if (preg_match('/circulation=\"([0-9]+)\"/i', $result, $match)) {
					return (int) $match[1];
				}
			}
		}	

		return -1;
	}
	
	function google_backlinks() {
		if ($this->snoopy->fetch('http://www.google.com/search?q=link%3A' . urlencode($this->blog_url))) {
			if (preg_match('/of about <b>([0-9\.,]+)<\/b> linking/si', $this->snoopy->results, $match)) {
				return (int) str_replace(array('.', ','), '', $match[1]);
			} else if (preg_match('/of <b>([0-9\.,]+)<\/b> linking/si', $this->snoopy->results, $match)) {
				return (int) str_replace(array('.', ','), '', $match[1]);
			}
		}
	
		return 0;
	}
	
	function yahoo_backlinks() {
		if ($this->snoopy->fetch('http://siteexplorer.search.yahoo.com/search?p=' . urlencode($this->blog_url) . '&bwm=i')) {
			if (preg_match('/<span class="btn">Inlinks \(([0-9,]+)\)/si', $this->snoopy->results, $match)) {
				return (int) str_replace(',', '', $match[1]);
			}
		}
	
		return 0;
	}
	
	function delicious_bookmarks() {			
		if ($this->snoopy->fetch('http://delicious.com/url/check?url=' . urlencode($this->blog_url) .'&submit=check%20url')) {
			if (preg_match('/People have saved this <span[^>]*>([0-9,]+)<\/span>/i', $this->snoopy->results, $match)) {
				return (int) str_replace(',', '', $match[1]);
			}	
		}

		return 0;
	}
}

function _bs_str_to_num($Str, $Check, $Magic) {
	$Int32Unit = 4294967296;  // 2^32

	$length = strlen($Str);
	for ($i = 0; $i < $length; $i++) {
		$Check *= $Magic; 	
		if ($Check >= $Int32Unit) {
			$Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit));
			$Check = ($Check < -2147483648) ? ($Check + $Int32Unit) : $Check;
 		}
		$Check += ord($Str{$i}); 
	}
	return $Check;
}

function _bs_hash_url($String) {
	$Check1 = _bs_str_to_num($String, 0x1505, 0x21);
	$Check2 = _bs_str_to_num($String, 0, 0x1003F);

	$Check1 >>= 2; 	
	$Check1 = (($Check1 >> 4) & 0x3FFFFC0 ) | ($Check1 & 0x3F);
	$Check1 = (($Check1 >> 4) & 0x3FFC00 ) | ($Check1 & 0x3FF);
	$Check1 = (($Check1 >> 4) & 0x3C000 ) | ($Check1 & 0x3FFF);	

	$T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) <<2 ) | ($Check2 & 0xF0F );
	$T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000 );

	return ($T1 | $T2);
}

function _bs_check_hash($Hashnum) {
	$CheckByte = 0;
	$Flag = 0;

	$HashStr = sprintf('%u', $Hashnum) ;
	$length = strlen($HashStr);

	for ($i = $length - 1;  $i >= 0;  $i --) {
		$Re = $HashStr{$i};
		if (1 === ($Flag % 2)) {              
			$Re += $Re;     
			$Re = (int)($Re / 10) + ($Re % 10);
		}
		$CheckByte += $Re;
		$Flag ++;	
	}

	$CheckByte %= 10;
	if (0 !== $CheckByte) {
		$CheckByte = 10 - $CheckByte;
		if (1 === ($Flag % 2) ) {
			if (1 === ($CheckByte % 2)) {
				$CheckByte += 9;
			}
			$CheckByte >>= 1;
		}
	}

	return '7'.$CheckByte.$HashStr;
}

function _bs_get_ch($url) { 
	return _bs_check_hash(_bs_hash_url($url)); 
}
?>