<?php

/**
 * @author Kirk Mayo <kirk.mayo@solnet.co.nz>
 *
 * Test the extra site config settings for this module
 */

class SocialMediaSettingsTest extends FunctionalTest {

	protected static $fixture_file = 'SocialMediaSettingsTest.yml';
	
	public function testFacebookSettings() {
		SS_Datetime::set_mock_now('2016-02-29 00:00:00');
		$config = $this->objFromFixture('SiteConfig', 'media');
		$this->assertEquals($config->FacebookAppId, 'fbApp1234');
		$this->assertEquals($config->FacebookPageId, 'fbPage1234');
		$this->assertEquals($config->FacebookSecret, 'fbSecret1234');
		$this->assertEquals($config->FacebookAccessToken, 'fbAccessToken1234');
		$this->assertEquals($config->FacebookAccessTokenClear, false);

		// is the date in the future
		$tokenDate = strtotime($config->FacebookAccessTokenDate);
		$mockedDate = strtotime(SS_DateTime::now());
		$this->assertEquals($tokenDate, $mockedDate);
	}

	public function testLinkedInSettings() {
		$config = $this->objFromFixture('SiteConfig', 'media');
		$this->assertEquals($config->LinkedInId, 'ln1234');
		$this->assertEquals($config->LinkedInSecret, 'lnSecret1234');
		$this->assertEquals($config->LinkedInAccessToken, 'lnAccessToken1234');
		$this->assertEquals($config->LinkedInAccessTokenClear, false);
	}

	public function testTwitterSettings() {
		$config = $this->objFromFixture('SiteConfig', 'media');
		$this->assertEquals($config->TwitterConsumerKey, 'tKey1234');
		$this->assertEquals($config->TwitterConsumerSecret, 'tSecret1234');
		$this->assertEquals($config->TwitterAccessToken, 'tAccessToken1234');
		$this->assertEquals($config->TwitterAccessSecret, 'tAccessSecret1234');
	}
}
