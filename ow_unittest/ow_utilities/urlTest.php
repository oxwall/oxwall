<?php

class UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test secure uri (All uris must be encoded - preventing XSS)
     */
    public function testSecureUriAdmin()
    {
        $uriItems = array(
            array(
                'xss' => '/oxwall_repo/groups/invitation',
                'cleaned' => '/oxwall_repo/groups/invitation' 
            ),
            array(
                'xss' => '/oxwall_repo/groups/invitation/"><script>alert(\'s\')</script>',
                'cleaned' => '/oxwall_repo/groups/invitation/%22%3E%3Cscript%3Ealert%28%27s%27%29%3C/script%3E' 
            ),
            array(
                'xss' => '/oxwall_repo/groups/invitation?test=aa&h=/"><script>alert(10)</script>',
                'cleaned' => '/oxwall_repo/groups/invitation?test=aa&h=%2F%22%3E%3Cscript%3Ealert%2810%29%3C%2Fscript%3E' 
            ),
            array(
                'xss' => '/oxwall_repo/groups/invitation?g/"><script>alert(10)</script>',
                'cleaned' => '/oxwall_repo/groups/invitation?g%2F%22%3E%3Cscript%3Ealert%2810%29%3C%2Fscript%3E=' 
            )
        );

        foreach ($uriItems as $uri) 
        {
            $this->assertEquals($uri['cleaned'], UTIL_Url::secureUri($uri['xss']));
        }
    }

    /**
     * Test secure url (All urls must be encoded - preventing XSS)
     */
    public function testSecureUrlAdmin()
    {
        $urlItems = array(
            array(
                'xss' => 'http://test.com/groups/invitation',
                'cleaned' => 'http://test.com/groups/invitation' 
            ),
            array(
                'xss' => 'http://192.168.0.1:8080',
                'cleaned' => 'http://192.168.0.1:8080' 
            ),
            array(
                'xss' => 'http://192.168.0.1:8080/#anchor"><script>alert(10)</script>',
                'cleaned' => 'http://192.168.0.1:8080/#anchor%22%3E%3Cscript%3Ealert%2810%29%3C%2Fscript%3E' 
            )
        );

        foreach ($urlItems as $url) 
        {
            $this->assertEquals($url['cleaned'], UTIL_Url::secureUrl($url['xss']));
        }
    }
}