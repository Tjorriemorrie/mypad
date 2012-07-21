<?php

namespace My\PadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StatsController extends Controller
{
    /**
     * @Route("/stats/load")
     * @Template()
     */
    public function indexAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();

    	$songstop = $em->getRepository('MyPadBundle:Song')->getLadder('top');
    	$songslow = $em->getRepository('MyPadBundle:Song')->getLadder('low');

    	$artiststop = $em->getRepository('MyPadBundle:Artist')->getLadder('top');
    	$artistslow = $em->getRepository('MyPadBundle:Artist')->getLadder('low');

    	$albumstop = $em->getRepository('MyPadBundle:Album')->getLadder('top');
    	$albumslow = $em->getRepository('MyPadBundle:Album')->getLadder('low');

    	return array('songstop'=>$songstop, 'songslow'=>$songslow, 'artiststop'=>$artiststop, 'artistslow'=>$artistslow, 'albumstop'=>$albumstop, 'albumslow'=>$albumslow);
    }


    /**
     * @Route("/tabs/{tab}", name="stats_tab", defaults={"tab"="artiststop"})
     */
    public function tabsAction($tab)
    {
    	$em = $this->getDoctrine()->getEntityManager();

    	$limit = 28;

    	// artist
    	if (stripos($tab, 'artist') !== false) {
    		if (stripos($tab, 'top') !== false) $artists = $em->getRepository('MyPadBundle:Artist')->getLadder('top', $limit);
    		elseif (stripos($tab, 'low') !== false) $artists = $em->getRepository('MyPadBundle:Artist')->getLadder('low', $limit);
    		else die('unknown q');
			return $this->render('MyPadBundle:Stats:ladderartists.html.twig', array('artists'=>$artists));
    	}
    	// album
    	elseif (stripos($tab, 'album') !== false) {
    		if (stripos($tab, 'top') !== false) $albums = $em->getRepository('MyPadBundle:Album')->getLadder('top', $limit);
    		elseif (stripos($tab, 'low') !== false) $albums = $em->getRepository('MyPadBundle:Album')->getLadder('low', $limit);
    		else die('unknown q');
			return $this->render('MyPadBundle:Stats:ladderalbums.html.twig', array('albums'=>$albums));
    	}
    	// songs
    	elseif (stripos($tab, 'songs') !== false) {
	   		if (stripos($tab, 'top') !== false) $songs = $em->getRepository('MyPadBundle:Song')->getLadder('top', $limit);
    		elseif (stripos($tab, 'low') !== false) $songs = $em->getRepository('MyPadBundle:Song')->getLadder('low', $limit);
    		else die('unknown q');
			return $this->render('MyPadBundle:Stats:laddersongs.html.twig', array('songs'=>$songs));
    	} else die('unknown');
    }


    /**
     * @Route("/detail", name="detail")
     * @Template()
     */
    public function detailAction()
    {
       	// retrieve current song from session
    	$session = $this->get('session');
    	$songId = $session->get('inplay');

    	$em = $this->getDoctrine()->getEntityManager();
    	$song = $em->getRepository('MyPadBundle:Song')->find($songId);
    	if (!$song) throw $this->createNotFoundException('No song to play');

    	$similars = $em->getRepository('MyPadBundle:Similar')->getClose($song->getArtist());

    	return array('song'=>$song, 'similars'=>$similars);
    }


    /**
     * Playlist
     */
    public function historyAction()
    {
    	try {
	    	$this->view->history = $this->em->getRepository('\My\Entity\Song')->getHistory($this->session->history);
    	} catch (Exception $e) {
    		$this->view->history = $this->session->history = array();
    	}
    }


    /**
     * Shows tip on what to do with library
     * @Route("/tip", name="tip")
     * @Template()
     */
    public function tipAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();

    	// is there unplayed songs?
    	$unplayed = $em->getRepository('MyPadBundle:Song')->countUnplayed();
    	if ($unplayed > 0) return array('tip' => 'There is ' . $unplayed . ' songs left', 'entityId' => null);

   		// unalbumed songs
		$tip = $em->getRepository('MyPadBundle:Song')->getSongNotInAlbum();
    	if (!is_null($tip)) return $tip;

    	// is albums complete?
		$tip = $em->getRepository('MyPadBundle:Album')->isAlbumsComplete();
		if (!is_null($tip)) return $tip;

   		// unslotted albums
    	$tip = $em->getRepository('MyPadBundle:Album')->getAlbumNotSlotted();
    	if (!is_null($tip)) return $tip;

    	// remove unliked and unlistened album
    	$tip = $em->getRepository('MyPadBundle:Album')->getAlbumToRemove();
    	if (!is_null($tip)) return $tip;

    	// most popular artist
    	$tip = $em->getRepository('MyPadBundle:Artist')->getTopArtistToAdd();
    	if (!is_null($tip)) return $tip;

    	return array('tip' => date('H:i:s'), 'entityId' => null);
    }


    /**
     * Save slots of album
     * @Route("/setslots/{id}/{slots}", name="set_slots", defaults={"id"=null, "slots"=null})
     */
    public function slotsAction($id, $slots)
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	$song = $em->getRepository('MyPadBundle:Song')->find($id);
    	if (!$song) return new Response('Song id not found: ' . $id);

    	$album = $song->getAlbum();
    	if (!$album) return new Response('Album for song not found: ' . $id);

    	if (empty($slots)) $slots = null;
    	$album->setSlots($slots);
    	$em->flush();

    	return new Response('...');
    	//return $this->redirect($this->generateUrl('tip'));
    }


    /**
     * Save name of album of song
     * @Route("/namealbum/{id}/{name}", name="name_album", defaults={"id"=null, "name"=null})
     */
    public function namealbumAction($id, $name)
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	$song = $em->getRepository('MyPadBundle:Song')->find($id);
    	if (!$song) return new Response('Song id not found: ' . $id);

    	// album artist
   		if (is_null($song->getArtist())) {
	   		$album = $em->getRepository('MyPadBundle:Album')->findOneBy(array('title'=>$name));
	   		if (!$album) {
		   		$album = new \My\PadBundle\Entity\Album();
		   		$em->persist($album);
	   		}
   		} else {
	   		$album = $em->getRepository('MyPadBundle:Album')->findOneBy(array('title'=>$name, 'artist'=>$song->getArtist()->getId()));
  			if (!$album) {
	   			$album = new \My\PadBundle\Entity\Album();
	   			$em->persist($album);
	 			$album->setArtist($song->getArtist());
   			}
		}
		$album->setTitle($name);
		$song->setAlbum($album);

    	$em->flush();
    	return new Response('...');
    }


    /**
     * @Route("/check")
     */
    public function checkAction()
    {
    	$session = $this->get('session');
    	$check = $session->get('check');
    	if (!$check) $check = array();
    	else $check = unserialize($check);
    	die(print_r($check));

    	$em = $this->getDoctrine()->getEntityManager();
    	$albums = $em->getRepository('MyPadBundle:Album')->findAll();

    	foreach ($albums as $album) {
    		$id = $album->getId();
    		if ($id == 1) continue;

    		if (isset($check[$id])) {
    			if ($check[$id] == 'checking') {
    				$check[$id] = 'error at album ' . $id;
    				die(print_r($check));
    			} elseif ($check[$id] != 'ok') {
    				$check[$id] = 'wtf is going on at ' . $id;
    				die(print_r($check));
    			}
    		}

    		$check[$id] = 'checking';
			$session->set('check', serialize($check));

			$songs = $album->getSongs();
			$count = $album->getSongs()->count();

			$check[$id] = 'ok';
			$session->set('check', serialize($check));
    	}

    	die(print_r($check));
    }


    /**
     * @Route("/artist/wait/{id}")
     */
    public function artistwaitAction($id)
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	$artist = $em->getRepository('MyPadBundle:Artist')->find($id);

    	$artist->setFullCounter($artist->getFullCounter() + 1);
    	$artist->setFullAt(new \DateTime('+' . $artist->getFullCounter() . ' months'));
    	$em->flush();

    	return new Response(json_encode('Ignoring ' . $artist->getName() . ' till ' . $artist->getFullAt()->format('Y-m-d')));
    }
}
