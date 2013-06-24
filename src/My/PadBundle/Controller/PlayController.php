<?php

namespace My\PadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use My\PadBundle\Entity\Song;


class PlayController extends Controller
{
    /**
     * @Route("/play", name="play")
     * @Template()
     */
    public function indexAction()
    {
		$em = $this->getDoctrine()->getEntityManager();
// 	    $song = $em->getRepository('MyPadBundle:Song')->findOneByPlaycount(0);
// 	    if (!$song) {
	    	$song = $em->getRepository('MyPadBundle:Song')->getRandomPrioritised();
// 	    }

	    $session = $this->get('session');
     	$session->set('inplay', $song->getId());

        // bad-play threshold
        // end song as if played
//        if ($song->getRating() <= 0.40 && $song->getPlaycount() >= 1 ||
//            $song->getRating() <= 0.60 && $song->getPlaycount() >= 2 ||
//            $song->getRating() <= 0.67 && $song->getPlaycount() >= 3) {
//            return $this->redirect($this->generateUrl('postplay'));
//        }

     	return array('song' => $song);
    }


    /**
     * @Route("/postplay", name="postplay")
     */
    public function postplayAction()
    {
       	// retrieve current song from session
    	$session = $this->get('session');
    	$songId = $session->get('inplay');

    	$em = $this->getDoctrine()->getEntityManager();
    	$song = $em->getRepository('MyPadBundle:Song')->find($songId);
    	if (!$song) throw $this->createNotFoundException('No song to play');

	    if (!is_null($song->getTitle()) && !is_null($song->getArtist())) {
			$maxPlaycount = $em->getRepository('MyPadBundle:Song')->getMaxPlaycount();
	        $song->postplay($maxPlaycount);
	        $em->getRepository('MyPadBundle:Album')->checkArtist($song);
			$em->flush();
	    }

		return $this->redirect($this->generateUrl('play'));
	}


    /**
     * @Route("/info", name="info")
     * @Template()
     */
    public function infoAction()
    {
       	// retrieve current song from session
    	$session = $this->get('session');
    	$songId = $session->get('inplay');

    	$em = $this->getDoctrine()->getEntityManager();
    	$song = $em->getRepository('MyPadBundle:Song')->find($songId);
    	if (!$song) throw $this->createNotFoundException('No song to play');

    	$artists = $em->getRepository('MyPadBundle:Artist')->getAutocomplete();
    	$albums = $em->getRepository('MyPadBundle:Album')->getAutocomplete();

    	return array('song'=>$song, 'artists'=>$artists, 'albums'=>$albums);
    }


	/**
	 * @Route("/decisions", name="decisions")
	 * @Template()
	 */
	public function decisionsAction()
	{
		return array();
	}


	////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////

	/**
	 * @Route("/getrating", name="get_rating")
	 * @Template()
	 */
	public function getratingAction()
	{
       	// retrieve current song from session
    	$session = $this->get('session');
    	$songId = $session->get('inplay');

    	$em = $this->getDoctrine()->getEntityManager();
    	$song = $em->getRepository('MyPadBundle:Song')->find($songId);
    	if (!$song) throw $this->createNotFoundException('No song to play');

        do {
    	    $compete = $em->getRepository('MyPadBundle:Rating')->getSongToRate($song);
        } while ($compete && $em->getRepository('MyPadBundle:Rating')->hasCompeted($song, $compete));

    	$em->clear();
    	return array('compete'=>$compete);
	}


	/**
	 * @Route("/setrating", name="set_rating", defaults={"_format"="json"})
	 */
	public function setratingAction(Request $request)
	{
		$id = $request->query->get('id', null);
		$choice = $request->query->get('choice', null);

       	// retrieve current song from session
    	$session = $this->get('session');
    	$songId = $session->get('inplay');

    	$em = $this->getDoctrine()->getEntityManager();
    	$song = $em->getRepository('MyPadBundle:Song')->find($songId);
    	if (!$song) return new Response(json_encode('Playing song could not be found: ' . $songId));

    	$compete = $em->getRepository('MyPadBundle:Song')->find($id);
    	if (!$compete) return new Response(json_encode('Competing song could not be found: ' . $id));

		$maxPlaycount = $em->getRepository('MyPadBundle:Song')->getMaxPlaycount();
    	$rating = new \My\PadBundle\Entity\Rating();
    	$em->persist($rating);
    	if ($choice == 'Better') {
    		$rating->setWinner($compete);
			$rating->setLoser($song);
			$song->updateRatings($maxPlaycount, 0);
			$compete->updateRatings($maxPlaycount, 1);
		} elseif ($choice == 'Worse') {
			$rating->setWinner($song);
			$rating->setLoser($compete);
			$song->updateRatings($maxPlaycount, 1);
			$compete->updateRatings($maxPlaycount, 0);
		} else return new Response(json_encode('Could not understand choice: ' . $choice));
		$em->flush();

		return new Response(json_encode('win'));
	}


	////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////


	/**
	 * @Route("/getsimilar", name="get_similar")
	 * @Template()
	 */
	public function getsimilarAction()
	{
       	// retrieve current song from session
    	$session = $this->get('session');
    	$songId = $session->get('inplay');

    	$em = $this->getDoctrine()->getEntityManager();
    	$song = $em->getRepository('MyPadBundle:Song')->find($songId);
    	if (!$song) throw $this->createNotFoundException('No song to play');

    	$similars = $em->getRepository('MyPadBundle:Similar')->getSelection($song);
    	$em->clear();
    	return array('similars'=>$similars);
	}


	/**
	 * @Route("/setsimilar", name="set_similar", defaults={"_format"="json"})
	 */
	public function setsimilarAction(Request $request)
	{
		$winnerId = $request->query->get('winner', null);
		$loserId = $request->query->get('loser', null);

       	// retrieve current song from session
    	$session = $this->get('session');
    	$songId = $session->get('inplay');

    	$em = $this->getDoctrine()->getEntityManager();
    	$song = $em->getRepository('MyPadBundle:Song')->find($songId);
    	if (!$song) return new Response(json_encode('Playing song could not be found: ' . $songId));
    	if (is_null($song->getArtist())) return new Response(json_encode('Playing song has no artist: ' . $songId));
    	$artist = $song->getArtist();
    	$artist->setSimilarAt(new \DateTime());

    	$winner = $em->getRepository('MyPadBundle:Artist')->find($winnerId);
    	if (!$winner) return new Response(json_encode('Winner artist could not be found: ' . $winnerId));
    	$winner->setSimilarAt(new \DateTime());

    	$loser = $em->getRepository('MyPadBundle:Artist')->find($loserId);
    	if (!$loser) return new Response(json_encode('Loser artist could not be found: ' . $loserId));
    	$loser->setSimilarAt(new \DateTime());

    	$similar = new \My\PadBundle\Entity\Similar();
    	$similar->setArtistMain($artist);
    	$similar->setArtistGood($winner);
    	$similar->setArtistBad($loser);
    	$em->persist($similar);
		$em->flush();

		return new Response(json_encode('win'));
	}


	////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////


	/**
     * @Route("/info/save", name="info_save", defaults={"_format"="json"})
     */
    public function saveAction(Request $request)
    {
    	if (!$request->isXmlHttpRequest()) return new Response(json_encode('Not a XmlHttpRequest'));

       	// retrieve current song from session
    	$session = $this->get('session');
    	$songId = $session->get('inplay');

    	$em = $this->getDoctrine()->getEntityManager();
    	$song = $em->getRepository('MyPadBundle:Song')->find($songId);
    	if (!$song) return new Response(json_encode('No song to play for id: ' . $songId));

    	$name = $request->query->get('name', null);
    	$value = stripslashes($request->query->get('value', null));
    	//die('name = ' . $name);
    	//die('value = ' . $value);

    	$logger = $this->get('logger');
    	//$logger->info($name);
    	//$logger->info($value);

    	// title
    	if ($name == 'title') {
	   		$song->setTitle($value);
	   		$logger->info('Title set as: ' . $song->getTitle());
    	}

    	// track
    	if ($name == 'track') {
    		if (empty($value)) $song->setTrack(null);
	   		else $song->setTrack($value);
    		$logger->info('Track set as: ' . $song->getTrack());
    	}

   		// artist
    	if ($name == 'artist') {
	   		if (empty($value)) $song->setArtist(null);
	   		else {
	   			$artist = $em->getRepository('MyPadBundle:Artist')->findOneByName($value);
	   			if (!$artist) {
		   			$artist = new \My\PadBundle\Entity\Artist();
		   			$em->persist($artist);
	   			}
	 			$artist->setName($value);
	   			$song->setArtist($artist);
	   		}
	   		$logger->info('Artist set as: ' . $artist->getName());
    	}

   		// album
   		if ($name == 'album') {
	   		if (empty($value)) $song->setAlbum(null);
	   		else {
	   			// album artist
	   			if (is_null($song->getArtist())) {
		   			$album = $em->getRepository('MyPadBundle:Album')->findOneBy(array('title'=>$value));
		   			if (!$album) {
			   			$album = new \My\PadBundle\Entity\Album();
			   			$em->persist($album);
		   			}
	   			} else {
		   			$album = $em->getRepository('MyPadBundle:Album')->findOneBy(array('title'=>$value, 'artist'=>$song->getArtist()->getId()));
		   			if (!$album) {
			   			$album = new \My\PadBundle\Entity\Album();
			   			$em->persist($album);
			 			$album->setArtist($song->getArtist());
		   			}
	   			}
	 			$album->setTitle($value);

	   			// album released at
	   			if (is_null($album->getReleasedAt()) && !is_null($song->getAlbum())) {
	   				if (!is_null($song->getAlbum()->getReleasedAt())) {
	   					$album->setReleasedAt($song->getAlbum()->getReleasedAt());
	   				}
	   			}

	   			$song->setAlbum($album);
	   		}
	   		$logger->info('Album set as: ' . $album->getTitle());
   		}

   		// year
		if ($name == 'year') {
			if (!is_null($song->getAlbum())) {
				if (empty($value)) $song->getAlbum()->setReleasedAt(null);
				else $song->getAlbum()->setReleasedAt(new \DateTime(date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, $value))));
				$logger->info('ReleasedAt set as: ' . $song->getAlbum()->getReleasedAt()->format('Y-m-d'));
			}
		}

		$em->flush();

		if ($name == 'album') {
			if (!is_null($album->getReleasedAt())) return new Response(json_encode(array('album'=>'yes', 'year'=>$album->getReleasedAt()->format('Y'))));
		}
		return new Response(json_encode($name));
    }
}
