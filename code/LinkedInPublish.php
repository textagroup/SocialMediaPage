<?php

class LinkedinPublish extends Controller {
	public function postMessage($message) {
		//Share on linkedIn
		$siteConfig = SiteConfig::current_site_config();

		$linkedIn = new Happyr\LinkedIn\LinkedIn(
			$siteConfig->LinkedInId,
			$siteConfig->LinkedInSecret
		);
		$accessToken = $siteConfig->LinkedInAccessToken;

		$linkedIn->setAccessToken($accessToken);

		if ($linkedIn->isAuthenticated()) {
			//we know that the user is authenticated now. Start query the API
			$options = array(
				'json' => array(
					'comment' => $message,
					'visibility' => array(
						'code' => 'anyone'
					)
				)
			);
			$result = $linkedIn->post('v1/people/~/shares', $options);
			if ($linkedIn->hasError()) {
				$msg = $linkedIn->hasError();
				SS_Log::log($msg, SS_Log::ERR);
				return false;
			}
			return true;
		} else {
			//if not authenticated
			$url = $linkedIn->getLoginUrl();
			return "<a href='$url'>" .
				_t('SocialMediaPage.LOGINTOLINKEDIN', 'Login to LinkedIn') .
				'</a>';
		}
	}
} 
