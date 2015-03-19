<?php

class UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test uri xss preventing
     */
    public function testUriXssPreventing()
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
}