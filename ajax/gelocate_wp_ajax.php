<?php 
header("Content-type: application/json");
require_once('../inc/TwitterAPIExchange.php');
require_once('../../../../wp-load.php');
  date_default_timezone_set('America/New_York');
  $respond = array();
function parse_message( $tweet ) {
	if ( !empty($tweet->entities) ) {
		$replace_index = array();
		$append = array();
		$text = $tweet->text;
		foreach ($tweet->entities as $area => $items) {
			$prefix = false;
			$display = false;
			switch ( $area ) {
				case 'hashtags':
					$find   = 'text';
					$prefix = '#';
					$url    = 'https://twitter.com/search/?src=hash&q=%23';
					break;
				case 'user_mentions':
					$find   = 'screen_name';
					$prefix = '@';
					$url    = 'https://twitter.com/';
					break;
				case 'media':
					$display = 'media_url_https';
					$href    = 'media_url_https';
					$size    = 'small';
					break;
				case 'urls':
					$find    = 'url';
					$display = 'display_url';
					$url     = "expanded_url";
					break;
				default: break;
			}
			foreach ($items as $item) {
				if ( $area == 'media' ) {
					// We can display images at the end of the tweet but sizing needs to added all the way to the top.
					// $append[$item->$display] = "<img src=\"{$item->$href}:$size\" />";
				}else{
					$msg     = $display ? $prefix.$item->$display : $prefix.$item->$find;
					$replace = $prefix.$item->$find;
					$href    = isset($item->$url) ? $item->$url : $url;
					if (!(strpos($href, 'http') === 0)) $href = "http://".$href;
					if ( $prefix ) $href .= $item->$find;
					$with = "<a target='_blank' href=\"$href\">$msg</a>";
					$replace_index[$replace] = $with;
				}
			}
		}
		foreach ($replace_index as $replace => $with) $tweet->text = str_replace($replace,$with,$tweet->text);
		foreach ($append as $add) $tweet->text .= $add;
	}
	$return = $tweet;
	return $tweet;
}
function getTwitterStatus($userid){	
	/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
	$settings = array(
	    'oauth_access_token' => "17640323-uaDKwONW4A9VOnGOqhmGTfbvqJyQ4XG9wZBGKzuDF",
	    'oauth_access_token_secret' => "SUrWYLBtDl6VDbIkkd3CS8tM2wptHOy0m8tkRks4P2x6q",
	    'consumer_key' => "tmqKP4cnpx8x6IxuGYZTvo9mE",
	    'consumer_secret' => "UA1mbUtHiwYIzuPIJNHAzAIby3dCu7T2zZ49nxc6sSAsq7Zj6l"
	);
	
	/** URL for REST request, see: https://dev.twitter.com/docs/api/1.1/ **/
	$url = 'https://api.twitter.com/1.1/blocks/create.json';
	$requestMethod = 'POST';
	
	/** POST fields required by the URL above. See relevant docs as above **/
	$postfields = array(
	    'screen_name' => 'rclations', 
	    'skip_status' => '1'
	);
	
	/** Perform a POST request and echo the response **/
	/*
	$twitter = new TwitterAPIExchange($settings);
	echo $twitter->buildOauth($url, $requestMethod)
	             ->setPostfields($postfields)
	             ->performRequest();
	*/
	/** Perform a GET request and echo the response **/
	/** Note: Set the GET field BEFORE calling buildOauth(); **/
	/*
	$url = 'https://api.twitter.com/1.1/followers/ids.json';
	$getfield = '?screen_name=oleary';
	$requestMethod = 'GET';
	$twitter = new TwitterAPIExchange($settings);
	echo $twitter->setGetfield($getfield)
	             ->buildOauth($url, $requestMethod)
	             ->performRequest();
	*/
	$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
	$requestMethod = 'GET';

	$getfield = '?screen_name='.$userid.'&count=1';
	
	$twitter = new TwitterAPIExchange($settings);
	$response = $twitter->setGetfield($getfield)
	                    ->buildOauth($url, $requestMethod)
	                    ->performRequest();
	$jsonobj = json_decode($response);
	
	if($response != null){

		foreach($jsonobj as $iteme){
                    global $respond;
			$item = parse_message($iteme);
			$id = $item->id_str;
			$created_at = $item->created_at;
			$created_at = strtotime($created_at);
			$mysqldate = date('F j, Y, g:i a',$created_at);
			if(isset($item->from_user)) {
				$from_user = $item->from_user;
			}
			$text = $item->text;
			$source = $item->source;
			if(isset($item->coordinates)) {
				$long = $item->coordinates->coordinates[0];
				$lat = $item->coordinates->coordinates[1];
			}
			if(isset($item->iso_language_code)) {
				$iso_language_code = $item->iso_language_code;
			} else {
				$iso_language_code = '';
			}
			
				$profile_image_url = $item->user->profile_image_url;
			
			
			if(isset($item->to_user_id)) {
				$to_user_id = $item->to_user_id;
			} else {
				$to_user_id = '';
			}
			if(isset($to_user_id)) {
				if($to_user_id==""){ $to_user_id = 0; }
			} else {
				$to_user_id = 0;
			}
			
			if(!empty($lat)) {
			 	$respond[] = "<div class='marker' data-icon='".$profile_image_url."' data-lat='$lat' data-lng='$long'><a target='_blank' href='https://twitter.com/".$userid."'><img class='twitlogo' src='".$profile_image_url."'><div class='tweet'><h4>$userid</h4></a><div>$text</div><div><em>$mysqldate<em></div></div></div>";
                        }
                }
        }
        
}


global $wpdb;
$results = $wpdb->get_results( 'SELECT * FROM wp_foodtrucks', ARRAY_A );

foreach($results as $result){
  getTwitterStatus($result['handle']);
  }
 
$response = array(
                 'success' => TRUE,
                 'data' => $respond
                  );

                  die( json_encode($response) );
