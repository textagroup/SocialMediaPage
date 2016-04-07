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
		$fields->addFieldToTab("Root.SocialMedia.Facebook",
			TextField::create(
				'FacebookAppId',
				_t('SocialMediaPage.FACEBOOKAPPID', 'Facebook App ID')
			)
		);
		$fields->addFieldToTab("Root.SocialMedia.Facebook",
			TextField::create(
				'FacebookPageId',
				_t('SocialMediaPage.FACEBOOKPAGEID', 'Facebook Page ID')
			)
		);
		$fields->addFieldToTab("Root.SocialMedia.Facebook",
			TextField::create(
				'FacebookSecret',
				_t('SocialMediaPage.FACEBOOKSECRET', 'Facebook Secret')
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
				$fields->addFieldToTab('Root.SocialMedia.Facebook',
					LiteralField::create(
						'FacebookAccessTokenExpires',
						_t('SocialMediaPage.TOKENEXPIRES',
							'Token expires in {tokenAge} days',
							array('tokenAge' => $tokenAge)
						)
					)
				);
				$fields->addFieldToTab('Root.SocialMedia.Facebook',
					CheckboxField::create(
						'FacebookAccessTokenClear',
						_t('SocialMediaPage.CLEARFACEBOOKACCESSTOKEN',
							'Clear Facebook Access Token'
						)
					)
				);
			} else {
				$fields->addFieldToTab('Root.SocialMedia.Facebook',
					LiteralField::create(
						'FacebookAccessTokenExpires',
						_t('SocialMediaPage.TOKENEXPIRED', 'Token has expired')
					)
				);
			}
		} else {
			$fields->addFieldToTab('Root.SocialMedia.Facebook',
				CheckboxField::create(
					'FacebookAccessTokenClear',
					_t('SocialMediaPage.CLEARFACEBOOKACCESSTOKEN',
						'Clear Facebook Access Token'
					)
				)
			);
			$fields->addFieldToTab('Root.SocialMedia.Facebook',
				LiteralField::create(
					'FacebookLogin',
					$validAccessToken
				)
			);
		}
		if ($this->owner->FacebookAccessToken) {
			$fields->addFieldToTab('Root.SocialMedia.Facebook',
				TextField::create('FacebookAccessToken',
					_t('SocialMediaPage.FACEBOOKACCESSTOKEN',
						'Facebook Access Token'
					),
					$this->owner->FacebookAccessToken
				)
			);
		}

		// LinkedIn fields
		$fields->addFieldToTab("Root.SocialMedia.LinkedInPublish",
			TextField::create(
				 'LinkedInId',
				_t('SocialMediaPage.LINKEDINPAGEID',
					'LinkedIn Page ID'
				)
			)
		);
		$fields->addFieldToTab("Root.SocialMedia.LinkedInPublish",
			TextField::create(
				'LinkedInSecret',
				_t('SocialMediaPage.LINKEDINSECRET',
					'LinkedIn Secret'
				)
			)
		);

		if ($this->owner->LinkedInAccessToken) {
			$fields->addFieldToTab('Root.SocialMedia.LinkedInPublish',
				TextField::create('LinkedInAccessToken',
					_t('SocialMediaPage.LINKEDINACCESSTOKEN',
						'LinkedIn Access Token'
					),
					$this->owner->LinkedInAccessToken
				)
			);
			$fields->addFieldToTab('Root.SocialMedia.LinkedInPublish',
				CheckboxField::create(
					'LinkedInAccessTokenClear',
					_t('SocialMediaPage.CLEARLINKEDINACCESSTOKEN',
						'Clear LinkedIn Access Token'
					)
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
							_t('SocialMediaPage.LINKEDINACCESSTOKEN',
								'LinkedIn Access Token'
							),
							$accessToken
						)
					);
				} else {
					$loginURL = $linkedIn->getLoginURL();
					$linkedinLogin = "<a href='$loginURL'>" .
						_t('SocialMediaPage.LOGINTOLINKEDIN',
							'Login to LinkedIn') .
						'</a>';
					$fields->addFieldToTab('Root.SocialMedia.LinkedInPublish',
						new LiteralField('LikedInLogin', $linkedinLogin)
					);
				}
			}
		}

		// Twitter settings
		$fields->addFieldsToTab('Root.SocialMedia.Twitter', array(
			HeaderField::create('TwitterHeader',
				_t('SocialMediaPage.TWITTERPUBLISHING',
					'Twitter Publishing')
			),
			LiteralField::create(
				'TwitterHelp', 
				_t('SocialMediaPage.TWITTERHELP',
					'Note that you will need to set up a separate Twitter ' .
					'App for each server you run a copy of the site on, ' .
					'eg. uat and prod.'
				)
			),
			TextField::create('TwitterConsumerKey', _t('SocialMediaPage.CONSUMERKEY',
				'Consumer Key')
			),
			TextField::create('TwitterConsumerSecret', _t('SocialMediaPage.CONSUMERSECRET',
				'Consumer Secret')
			),
			TextField::create('TwitterAccessToken', _t('SocialMediaPage.ACCESSTOKEN',
				'Access Token')
			),
			TextField::create('TwitterAccessSecret', _t('SocialMediaPage.ACCESSTOKENSECRET',
				'Access Token Secret')
			)
			->setDescription(
				_t('SocialMediaPage.TWITTERINFO',
					'Visit <a href="https://apps.twitter.com/">https://apps.twitter.com/</a> and log in as the user '.
					'maintaining the Twitter App for this site.<br>'.
					'You will find the required API details on the "Keys and Access Tokens" tab.<br><br>'.
					'<img src="{helpImage}_twitter-help.png">',
					array('helpImage' => $helpImage)
				)
			)
		));

		// Name the LinkedIn tab correctly
		$linkedInTab = $fields->findOrMakeTab('Root.SocialMedia.LinkedInPublish');
		$linkedInTab->setTitle('LinkedIn');
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
