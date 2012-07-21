<?php

namespace My\PadBundle\Tests\Entity;

use My\PadBundle\Entity\Song;

class SongTest extends \PHPUnit_Framework_TestCase
{
    public function testCanUpdateRatings()
    {
    	$song = new Song();
		$song->updateRatings(12, 1);
    }
}
