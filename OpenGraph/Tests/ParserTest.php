<?php

namespace OpenGraph\Tests;

require_once './OpenGraph/Parser.php';

use OpenGraph\Parser;

class ParserTest  extends \PHPUnit_Framework_TestCase
{
    public function testIMDB()
    {
        $html = file_get_contents('.//OpenGraph/Tests/Fixtures/imdb.com.html');

        $parser = new Parser($html);
        $parser->parse($html);

        $this->assertEquals($parser->title, 'IMDb - Movies, TV and Celebrities');
        $this->assertEquals($parser->url, 'http://www.imdb.com/');
        $this->assertEquals($parser->site_name, 'IMDb');

        return $parser;

    }

    /**
     * @depends testIMDB
     * @expectedException \Exception
     */
    public function testIMDBFailure(Parser $parser)
    {
        $parser->type; //type not defined, should throw error
    }

    /**
     * @depends testIMDB
     */
    public function testIsset(Parser $parser)
    {
        $this->assertTrue(isset($parser->title));
        $this->assertFalse(isset($parser->type));
    }

    public function testCanucks()
    {
        $html = file_get_contents('./OpenGraph/Tests/Fixtures/canucks.nh.com.html');

        $parser = new Parser($html);
        $parser->parse($html);

        $all = $parser->getAll();
        $this->assertCount(2, $all['image']);
        $this->assertCount(2, $parser->image);

        $parser->type;
    }

    public function testUrlDefaultArrays()
    {
        $html = <<<HTML
<meta property="og:image" content="http://example.com/rock.jpg" />
<meta property="og:image" content="http://example.com/rock2.jpg" />
<meta property="og:image:url" content="http://example.com/rock3.jpg" />
<meta property="og:image" content="http://example.com/rock4.jpg" />
HTML;

        $parser = new Parser($html);
        $parser->parse($html);

        $this->assertCount(4, $parser->image);
    }

    public function testStructureObjectArray()
    {
        $html = <<<HTML
<meta property="og:image" content="http://example.com/rock.jpg" />
<meta property="og:image" content="http://example.com/rock2.jpg" />
<meta property="og:image:width" content="300" />
<meta property="og:image:height" content="400" />

<meta property="og:image:url" content="http://example.com/rock3.jpg" />
<meta property="og:image:width" content="500" />
<meta property="og:image" content="http://example.com/rock4.jpg" />
HTML;

        $parser = new Parser($html);
        $parser->parse($html);

        $this->assertCount(4, $parser->image);
        $this->assertInternalType('array', $parser->image[1]);
        $this->assertInternalType('array', $parser->image[2]);
        $this->assertEquals(500, $parser->image[2]['width']);
        $this->assertInternalType('string', $parser->image[3]);
    }

    public function testBadStructuredObject()
    {
        $html = <<<HTML
<meta property="og:image:width" content="500" />
<meta property="og:image" content="http://example.com/rock.jpg" />
<meta property="og:image" content="http://example.com/rock2.jpg" />
<meta property="og:image:width" content="300" />
<meta property="og:image:height" content="400" />

<meta property="og:image:url" content="http://example.com/rock3.jpg" />

<meta property="og:image" content="http://example.com/rock4.jpg" />
HTML;
        $parser = new Parser($html);
        $parser->parse($html);
        $this->assertCount(4, $parser->image);
        $this->assertInternalType('string', $parser->image[0]);
    }
}
