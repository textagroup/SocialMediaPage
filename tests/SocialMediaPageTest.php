<?php

/**
 * @author Kirk Mayo <kirk.mayo@solnet.co.nz>
 *
 * Test the Social Media Page functions
 */

class SocialMediaPageTest extends FunctionalTest {

	protected static $fixture_file = 'SocialMediaPageTest.yml';
	
	public function testSocialMediaPage() {
		$page = SocialMediaPage::get()->first();
		$this->assertEquals($page->Title, 'Social Media Page');
		$this->assertEquals($page->Content, 'Social Media Page');
		$this->assertEquals($page->SocialMediaInfo, 'Social Media Page');
		$this->assertEquals($page->URLSegment, 'social-media-page');
		$this->assertEquals($page->FacebookPost, false);
		$this->assertEquals($page->LinkedInPost, false);
		$this->assertEquals($page->TwitterPost, false);
	}
}
