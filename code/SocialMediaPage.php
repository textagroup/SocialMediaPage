<?php
class SocialMediaPage extends Page {

	private static $db = array(
		'LinkedInPost' => 'Boolean',
		'FacebookPost' => 'Boolean',
		'TwitterPost' => 'Boolean',
		'SocialMediaInfo' => 'Varchar(128)'
	);

	private static $has_one = array(
	);

	public function getCMSFields(){
		
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Social', new CheckBoxField('FacebookPost'));
		$fields->addFieldToTab('Root.Social', new CheckBoxField('LinkedInPost'));
		$fields->addFieldToTab('Root.Social', new CheckBoxField('TwitterPost'));
		$fields->addFieldToTab('Root.Social', new TextField('SocialMediaInfo'));

		return $fields;
	}

	public function onAfterWrite() {
		$message = ($this->SocialMediaInfo)
			? $this->SocialMediaInfo
			: $this->Title;
		if ($this->ID && $this->FacebookPost == 1) {
			$facebook = singleton('FacebookPublish');
			$ret = $facebook->postMessage($this->AbsoluteLink(), $message);
			if ($ret !== true) {
				//Debug::Dump($ret); exit;
			}
			$this->FacebookPost = 0;
			$this->write();
		}

		//Share on linkedIn
		if ($this->ID && $this->LinkedInPost == 1) {
			$linkedIn = singleton('LinkedInPublish');
			$ret = $linkedIn->postMessage($message);
			if ($ret !== true) {
				//Debug::Dump($ret); exit;
			}
			$this->LinkedInPost = 0;
			$this->write();
		}

		//Share on twitter
		if ($this->ID && $this->TwitterPost == 1) {
			try {
				$twitter = singleton('TwitterPublish');
				$twitter->postMessage($message . ' ' . $this->AbsoluteLink());
				$this->TwitterPost = 0;
				$this->write();
			} catch (Exception $e) {
				// Ignore exceptions
			}
		}
		parent::onAfterWrite();
	}
}
class SocialMediaPage_Controller extends Page_Controller {
}
