<?php
ini_set('display_errors', 1);
require_once('TwitterAPIExchange.php');

/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
$settings = array(
    'oauth_access_token' => "1020473899-duRBnDasu1QgY8i2b6OOVYgnRiySwoNgAbyyX1R",
    'oauth_access_token_secret' => "Aju681Oboom9Ek9KfTlnmzkbJdgn3LmryomC7vbIhvYAQ",
    'consumer_key' => "ADNlvLYUrXXU5MGHH4128uvoJ",
    'consumer_secret' => "CtrnNwy4z4ii50Iuf6IaoQj3sn4n525h89xLFkbMQPEXElVD7I"
);

$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
$requestMethod = 'GET';
$requestParams = '?count=3';
$twitter = new TwitterAPIExchange($settings);
$tweets = $twitter->setGetfield($requestParams)->buildOauth($url, $requestMethod)->performRequest();
file_put_contents("tweets.json", $tweets);

$data = [];
$re = "/https?:\\/\\/t\\.co\\/[a-z\\.0-9]+/mi";
foreach(json_decode($tweets) as $tweet) {
    $temp = [
        'msg' => preg_replace($re, '', $tweet->text),
        'created_at' => $tweet->created_at,
        'display_name' => $tweet->user->name,
        'avatar' => $tweet->user->profile_image_url_https
    ];
    if (isset($tweet->entities->media[0])) {
        if ($tweet->entities->media[0]->type == 'photo') {
            $temp['image'] = $tweet->entities->media[0]->media_url_https;
        }
    }
    $data[] = $temp;

}
file_put_contents("data.json", json_encode($data));