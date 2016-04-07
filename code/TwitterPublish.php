<?php

class TwitterPublish extends Controller {
	public function postMessage($message) {
		$siteConfig = SiteConfig::current_site_config();
		// Post to Twitter feed
		\Codebird\Codebird::setConsumerKey(
			$siteConfig->TwitterConsumerKey,
			$siteConfig->TwitterConsumerSecret
		);
		$cb = \Codebird\Codebird::getInstance();
		$cb->setToken($siteConfig->TwitterAccessToken, $siteConfig->TwitterAccessSecret);
		try {
			// https://dev.twitter.com/rest/reference/post/statuses/update
			$ret = $cb->statuses_update(array(
				'status' => $message,
			));
			$this->TwitterStatus = 'Sent';
		} catch (Exception $e) {
			SS_Log::log('Twitter exception: '.$e->getMessage(), SS_Log::WARN);
		}
	}
}
