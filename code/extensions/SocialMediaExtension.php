<?php

class SocialMediaExtension extends DataExtension {
	private static $db = array(
		'LinkedInPost' => 'Boolean',
		'FacebookPost' => 'Boolean',
		'TwitterPost' => 'Boolean'
    );

	public function postSocialMedia($message = null, $pageLink = null) {
        $id = $this->owner->ID;
		if ($id && $this->owner->FacebookPost == 1) {
			$facebook = singleton('FacebookPublish');
			$ret = $facebook->postMessage($pageLink, $message);
			if ($ret !== true) {
				//Debug::Dump($ret); exit;
			}
			$this->owner->FacebookPost = 0;
			$this->owner->write();
		}

		//Share on linkedIn
		if ($id && $this->owner->LinkedInPost == 1) {
			$linkedIn = singleton('LinkedInPublish');
			$ret = $linkedIn->postMessage($message);
			if ($ret !== true) {
				//Debug::Dump($ret); exit;
			}
			$this->owner->LinkedInPost = 0;
			$this->owner->write();
		}

		//Share on twitter
		if ($id && $this->owner->TwitterPost == 1) {
			try {
				$twitter = singleton('TwitterPublish');
				$twitter->postMessage($message . ' ' . $pageLink);
				$this->owner->TwitterPost = 0;
				$this->owner->write();
			} catch (Exception $e) {
				// Ignore exceptions
			}
		}
	}
}
