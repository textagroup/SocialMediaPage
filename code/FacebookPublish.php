<?php

class FacebookPublish extends Controller {
	 private $fb;

	 public function __construct() {
		  $siteConfig = SiteConfig::current_site_config();
		  $appId = ($siteConfig->FacebookAppId)
				? $siteConfig->FacebookAppId
				: Config::inst()->get('FacebookApp', 'appId');
		  $secret = ($siteConfig->FacebookSecret)
				? $siteConfig->FacebookSecret
				: Config::inst()->get('FacebookApp', 'secret');
		  $accessToken = ($siteConfig->FacebookAccessToken)
				? $siteConfig->FacebookAccessToken
				: null;
		  if (empty($appId) || empty($secret)) return;

		  $this->fb = new Facebook\Facebook([
				'app_id' => $appId,
				'app_secret' => $secret,
				'default_graph_version' => 'v2.2'
		  ]);

		  if (!$accessToken) {
				$helper = $this->fb->getRedirectLoginHelper();
				$accessToken = $helper->getAccessToken();

				if (isset($accessToken)) {
					 // now extend the access token
					 $oAuth2Client = $this->fb->getOAuth2Client();
					 $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
					 $siteConfig->FacebookAccessToken = $accessToken->getValue();
					 $siteConfig->write();
				}
		  }

		  // check if we need to set a access token
		  $isTokenValid = $this->isAccessTokenValid();
	 }

	 public function isAccessTokenValid() {
		  $siteConfig = SiteConfig::current_site_config();
		  $accessToken = ($siteConfig->FacebookAccessToken)
				? $siteConfig->FacebookAccessToken
				: null;
		  if (!$accessToken && $this->fb) {
				$helper = $this->fb->getRedirectLoginHelper();
				$callback = Controller::join_links(
					 Director::absoluteBaseURL(),
					 Controller::curr()->link()
				);
				$loginURL = $helper->getLoginURL(
					 $callback,
					 array(
						  'req_perms' => 'manage_pages,publish_pages,publish_actions'
					 )
				);
				return "<a href='$loginURL'>Login to Facebook</a>";
		  }
		  return true;
	 }

	 public function postMessage($link, $message) {
		  $isTokenValid = $this->isAccessTokenValid();
		  if ($isTokenValid === true) {
				$linkData = [
					 'link' => $link,
					 'message' => $message
				];
				try {
					 $siteConfig = SiteConfig::current_site_config();
					 $accessToken = ($siteConfig->FacebookAccessToken)
						  ? $siteConfig->FacebookAccessToken
						  : null;
					 $pageId = ($siteConfig->FacebookPageId)
						  ? $siteConfig->FacebookPageId
						  : null;
					 // throw user error id no facebook page id is set
					 if ($pageId == null) {
						  return 'Facebook page ID must be set';
					 }
					 // get page access token
					 $response = $this->fb->get("/$pageId?fields=access_token", $accessToken);
					 $pageAccessToken = $response->getGraphPage()->getAccessToken();

					 // post to feed using page access token
					 $response = $this->fb->post("/$pageId/feed", $linkData, $pageAccessToken);
				} catch (Facebook\Exceptions\FacebookResponseException $e) {
					 echo 'Graph returned an error: ' . $e->getMessage();
					 exit;
				} catch (Facebook\Exceptions\FacebookSDKException $e) {
					 echo 'Facebook SDK returned an error: ' . $e->getMessage();
					 exit;
				}
		  }
		  return true;
	 }
} 
