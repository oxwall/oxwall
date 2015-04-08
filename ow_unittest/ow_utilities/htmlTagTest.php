<?php

class HtmlTagTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test auto link generation
     */
    public function testAutoLink()
    {
        $textItems = array(
            array(
                'raw_text' => '',
                'proccesed' => '' 
            ),
            array(
                'raw_text' => 'test',
                'proccesed' => 'test' 
            ),
            array(
                'raw_text' => 'http://77.96 test',
                'proccesed' => '<a href="http://77.96" rel="nofollow">http://77.96</a> test',
            ),
            array(
                'raw_text' => 'test test http://test.com test',
                'proccesed' => 'test test <a href="http://test.com" rel="nofollow">http://test.com</a> test' 
            ),
            array(
                'raw_text' => '<a href="http://test.com">http://test.com</a>',
                'proccesed' => '<a href="http://test.com">http://test.com</a>' 
            ),
            array(
                'raw_text' => '<a href=\'http://test.com\'>http://test.com</a>',
                'proccesed' => '<a href=\'http://test.com\'>http://test.com</a>' 
            ),
            array(
                'raw_text' => '<a href="http://test.com"><p>http://test.com</p></a> http://test.com',
                'proccesed' => '<a href="http://test.com"><p>http://test.com</p></a> <a href="http://test.com" rel="nofollow">http://test.com</a>' 
            ),
            array(
                'raw_text' => 'http://test.com/pages/page/_id/20?order=price&1=1',
                'proccesed' => '<a href="http://test.com/pages/page/_id/20?order=price&1=1" rel="nofollow">http://test.com/page...</a>',
                'label_length' => 20
            ),
            array(
                'raw_text' => '<a href="http://test.com?p=f#22"><span>First link</span></a>
                    <ul>
                        <li>test 1</li>
                    </ul>
                    <a href="http://test.com?param=1">
                        <ul>
                            <li>link test 1</li>
                            <li>link test 2</li>
                        </ul>
                    </a>
                    Some plain text
                    <a href="#">base</a>
                    Some plain text 2
                    <a><a></a></a>

                    Some plain text 3
                    http://www.oxwall.org/',

                'proccesed' => '<a href="http://test.com?p=f#22"><span>First link</span></a>
                    <ul>
                        <li>test 1</li>
                    </ul>
                    <a href="http://test.com?param=1">
                        <ul>
                            <li>link test 1</li>
                            <li>link test 2</li>
                        </ul>
                    </a>
                    Some plain text
                    <a href="#">base</a>
                    Some plain text 2
                    <a><a></a></a>

                    Some plain text 3
                    <a href="http://www.oxwall.org/" rel="nofollow">http://www.oxwall.org/</a>' 
            ),
            array(
                'raw_text' => 'http://77.96.17.78/openwack/ow_install/install/plugins',
                'proccesed' => '<a href="http://77.96.17.78/openwack/ow_install/install/plugins" rel="nofollow">http://77.96.17.78/openwack/ow_install/install/plugins</a>',
                'label_length' => 100
            ),
        );

        foreach ($textItems as $item) 
        {
            if ( !empty($item['label_length']) ) 
            {
                $this->assertEquals($item['proccesed'], UTIL_HtmlTag::autoLink($item['raw_text'], $item['label_length']));
            }
            else 
            {
                $this->assertEquals($item['proccesed'], UTIL_HtmlTag::autoLink($item['raw_text']));    
            }
        }

        file_put_contents('/home/esase/tmp/test.txt', UTIL_HtmlTag::autoLink($text));
    }
}