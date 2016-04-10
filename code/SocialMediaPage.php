<?php
class SocialMediaPage extends Page {

	private static $db = array(
		'SocialMediaInfo' => 'Varchar(128)'
	);

	private static $has_one = array(
	);

	public function getCMSFields(){
		
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Social', new CheckBoxField('FacebookPost',
			_t('SocialMediaPage.FACEBOOKPOST', 'Facebook Post'))
		);
		$fields->addFieldToTab('Root.Social', new CheckBoxField('LinkedInPost',
			_t('SocialMediaPage.LINKEDINPOST', 'LinkedIn Post'))
		);
		$fields->addFieldToTab('Root.Social', new CheckBoxField('TwitterPost',
			_t('SocialMediaPage.TWITTERPOST', 'Twitter Post'))
		);
		$fields->addFieldToTab('Root.Social', new TextField('SocialMediaInfo',
			_t('SocialMediaPage.SOCIALMEDIAINFO', 'Social Media Info'))
		);

		return $fields;
	}

    public function publish($data, $form) {
		$message = ($this->SocialMediaInfo)
			? $this->SocialMediaInfo
			: $this->Title;
        $this->postSocialMedia($message, $this->AbsoluteLink());
        parent::publish($data, $form);
	}
}
class SocialMediaPage_Controller extends Page_Controller {
}
