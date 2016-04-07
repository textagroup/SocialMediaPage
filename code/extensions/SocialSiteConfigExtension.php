<?php
/**
 * Add settings for publishing to Facebook, LinkedIn and Twitter
 */
class SocialSiteConfigExtension extends DataExtension {
	private static $db = array(
		'FacebookAppId' => 'Varchar',
		'FacebookPageId' => 'Varchar',
		'FacebookSecret' => 'Varchar',
		'FacebookAccessToken' => 'Text',
		'FacebookAccessTokenDate' => 'Date',
		'FacebookAccessTokenClear' => 'Boolean',
		//LinkedIn Details
		'LinkedInId' => 'Varchar',
		'LinkedInSecret' => 'Varchar',
		'LinkedInAccessToken' => 'Text',
		'LinkedInAccessTokenClear' => 'Boolean',
		//Twitter Details
		'TwitterConsumerKey' => 'Varchar',
		'TwitterConsumerSecret' => 'Varchar',
		'TwitterAccessToken' => 'Varchar',
		'TwitterAccessSecret' => 'Varchar'
	);

	public function updateCMSFields(FieldList $fields) {
		if (file_exists(
			SOCIALMEDIA_MODULE_DIR . '/images/' . i18n::get_locale() . '_twitter-help.png'
		)) {
			$helpImage = SOCIALMEDIA_MODULE_DIR . '/images/' . i18n::get_locale();
		} else {
			$helpImage = SOCIALMEDIA_MODULE_DIR . '/images/en_US';
		}
		$fields->addFieldToTab("Root.SocialMedia.FacebookPublish",
			TextField::create(
				'FacebookAppId',
				'Facebook App ID'
			)
		);
		$fields->addFieldToTab("Root.SocialMedia.FacebookPublish",
			TextField::create(
				'FacebookPageId',
				'Facebook Page ID'
			)
		);
		$fields->addFieldToTab("Root.SocialMedia.FacebookPublish",
			TextField::create(
				'FacebookSecret',
				'Facebook Secret'
			)
		);
		$facebook = singleton('FacebookPublish');
		$validAccessToken = $facebook->isAccessTokenValid();
		if ($validAccessToken === true) {
			$tokenExpires = strtotime($this->owner->FacebookAccessTokenDate . '
				+ 60 days');
			$now = strtotime('now');
			$tokenAge = floor(($tokenExpires - $now) / 86400);
			if ($tokenAge > 0 ) {
				$fields->addFieldToTab('Root.SocialMedia.FacebookPublish',
					LiteralField::create(
						'FacebookAccessTokenExpires',
						"Token expires in $tokenAge days"
					)
				);
				$fields->addFieldToTab('Root.SocialMedia.FacebookPublish',
					CheckboxField::create(
						'FacebookAccessTokenClear',
						'Clear Facebook Access Token'
					)
				);
			} else {
				$fields->addFieldToTab('Root.SocialMedia.FacebookPublish',
					LiteralField::create(
						'FacebookAccessTokenExpires',
						'Token has expired'
					)
				);
			}
		} else {
			$fields->addFieldToTab('Root.SocialMedia.FacebookPublish',
				CheckboxField::create(
					'FacebookAccessTokenClear',
					'Clear Facebook Access Token'
				)
			);
			$fields->addFieldToTab('Root.SocialMedia.FacebookPublish',
				LiteralField::create(
					'FacebookLogin',
					$validAccessToken
				)
			);
		}
		if ($this->owner->FacebookAccessToken) {
			$fields->addFieldToTab('Root.SocialMedia.FacebookPublish',
				TextField::create('FacebookAccessToken',
					'FacebookAccessToken',
					$this->owner->FacebookAccessToken
				)
			);
		}

		// LinkedIn fields
		$fields->addFieldToTab("Root.SocialMedia.LinkedInPublish",
			TextField::create(
				'LinkedInId',
				'LinkedIn Page ID'
			)
		);
		$fields->addFieldToTab("Root.SocialMedia.LinkedInPublish",
			TextField::create(
				'LinkedInSecret',
				'LinkedIn Secret'
			)
		);

		if ($this->owner->LinkedInAccessToken) {
			$fields->addFieldToTab('Root.SocialMedia.LinkedInPublish',
				TextField::create('LinkedInAccessToken',
					'LinkedInAccessToken',
					$this->owner->LinkedInAccessToken
				)
			);
			$fields->addFieldToTab('Root.SocialMedia.LinkedInPublish',
				CheckboxField::create(
					'LinkedInAccessTokenClear',
					'Clear LinkedIn Access Token'
				)
			);
		} else {

			if ($this->owner->LinkedInId && $this->owner->LinkedInSecret) {
				$linkedIn = new Happyr\LinkedIn\LinkedIn(
					$this->owner->LinkedInId,
					$this->owner->LinkedInSecret
				);

				// check if we can obtain a access token
				$accessToken = $linkedIn->getAccessToken();

				if ($accessToken) {
					$accessToken = $accessToken->getToken();
					$siteConfig = SiteConfig::current_site_config();
					$siteConfig->LinkedInAccessToken = $accessToken;
					$siteConfig->write();
					$fields->addFieldToTab('Root.SocialMedia.LinkedInPublish',
						TextField::create('LinkedInAccessToken',
							'LinkedIn Access Token',
							$accessToken
						)
					);
				} else {
					$loginURL = $linkedIn->getLoginURL();
					$linkedinLogin = "<a href='$loginURL'>Login to LinkedIn</a>";
					$fields->addFieldToTab('Root.SocialMedia.LinkedInPublish',
						new LiteralField('LikedInLogin', $linkedinLogin)
					);
				}
			}
		}

		// Twitter settings
		$fields->addFieldsToTab('Root.SocialMedia.Twitter', array(
			HeaderField::create('TwitterHeader', 'Twitter Publishing'),
			LiteralField::create(
				'TwitterHelp', 
				'Note that you will need to set up a separate Twitter App for each server you run a copy of the site on, '.
				'eg. uat and prod.'
			),
			TextField::create('TwitterConsumerKey', 'Consumer Key'),
			TextField::create('TwitterConsumerSecret', 'Consumer Secret'),
			TextField::create('TwitterAccessToken', 'Access Token'),
			TextField::create('TwitterAccessSecret', 'Access Token Secret')
			->setDescription(
				'Visit <a href="https://apps.twitter.com/">https://apps.twitter.com/</a> and log in as the user '.
				'maintaining the Twitter App for this site.<br>'.
				'You will find the required API details on the "Keys and Access Tokens" tab.<br><br>'.
				'<img src="'. $helpImage .'_twitter-help.png">'
			)
		));
	}

	public function populateDefaults() {
		parent::populateDefaults();
		$this->owner->FacebookAppId = Config::inst()->get('FacebookApp', 'appId');
		$this->owner->FacebookSecret = Config::inst()->get('FacebookApp', 'secret');
	}

	/*
	 * Do we need to clear the access token
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if ($this->owner->FacebookAccessTokenClear == 1) {
			$this->owner->FacebookAccessToken = null;
			$this->owner->FacebookAccessTokenClear = null;
		}
		if ($this->owner->LiknedInAccessTokenClear == 1) {
			$this->owner->LinkedInAccessToken = null;
			$this->owner->LinkedInAccessTokenClear = null;
		}
	}
}
