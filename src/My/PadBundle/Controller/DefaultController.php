<?php

namespace My\PadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }


    /**
     * @Route("/size/{for}", name="size")
     * @Template()
     */
    public function sizeAction($for)
    {
   		$em = $this->getDoctrine()->getEntityManager();
   		if ($for == 'songs') $size = $em->getRepository('MyPadBundle:Song')->getSize();
   		elseif ($for == 'artists') $size = $em->getRepository('MyPadBundle:Artist')->getSize();
   		elseif ($for == 'albums') $size = $em->getRepository('MyPadBundle:Album')->count();
   		else $this->createNotFoundException('Did not recognize for: ' . $for);

    	return array('size'=>$size, 'for'=>$for);
    }


    /**
     * @Route("/scan/{dir}", name="scan", defaults={"dir"=null})
     * @Template()
     */
    public function scanAction($dir)
    {
	    set_time_limit(0);
		$em = $this->getDoctrine()->getEntityManager();
		$added = $em->getRepository('MyPadBundle:Song')->scan($dir);
		$em->flush();
		$em->clear();
	    return array('added'=>$added, 'audio_path'=>AUDIO_PATH);
    }


    /**
     * @Route("/clean/{dir}", name="clean", defaults={"dir"=null})
     * @Template()
     */
    public function cleanAction($dir)
    {
		$em = $this->getDoctrine()->getEntityManager();
		$albums = $em->getRepository('MyPadBundle:Album')->clean();
		$artists = $em->getRepository('MyPadBundle:Artist')->clean();
		$songs = $em->getRepository('MyPadBundle:Song')->clean();
		$em->flush();
		$em->clear();
	    return array('audio_path'=>AUDIO_PATH, 'albums'=>$albums, 'artists'=>$artists, 'songs'=>$songs);
    }


    /**
     * @Route("/id3", name="id3")
     * @Template()
     */
    public function id3Action()
    {
    	$em = $this->getDoctrine()->getEntityManager();

    	$logger = $this->get('logger');

    	$found = false;
    	$id = 0;
    	while (!$found) {
	    	try {
    			$id++;
			    $song = $em->getRepository('MyPadBundle:Song')->find($id);
		    	$logger->info($song->getId());
	    		$logger->info($song->getPath());

    			$id3 = new \Zend_Media_Id3v2(AUDIO_PATH . '/' . $song->getPath());
    			$logger->info('id3 found!');
    			$logger->info('title = ' . $id3->tit2->text);
    			$found = true;
    		} catch (\Exception $e) {
    			//$id3 = new \Zend_Media_Id3v1();
    			//$logger->info('id3 not on file...creating new one');
    		}
    	}

    	//$id3->tit2->text = 'Shitter lifestyles';
//    	$id3->setTitle('blafoorbar');
//    	$id3->write(AUDIO_PATH . '/' . $song->getPath());
//    	$logger->info($id3);
//
//	    $frame = $id3->getFramesByIdentifier("TIT2"); // for song title; or TALB for album title; ..
//		$title = $frame[0]->getText();
//    	My_FirePHP::alert($title);
//
//    	$frame = $id3->getFramesByIdentifier("TALB"); // for song title; or TALB for album title; ..
//		$album = $frame[0]->getText();
//    	My_FirePHP::alert($album);
//
//		$frame = $id3->getFramesByIdentifier("APIC"); // for attached picture
//		$image = $frame[0]->getImageType();
//    	My_FirePHP::alert($image);
	    return array('bla'=>'foo');
    }


    /**
     * @Route("/syncs", name="sync")
     * @Template()
     */
    public function syncsAction()
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	$songs = $em->getRepository('MyPadBundle:Song')->getTop();

    	foreach ($songs as $song) {
    		if ($song->getCodec() != 'mp3') continue;
    		$filename = $song->getArtist()->getName() . ' - ' . $song->getTitle() . '.mp3';
    		copy(AUDIO_PATH . '/' . $song->getPath(), SYNC_PATH . '/' . $filename);
    	}

    	return array('size'=>count($songs));
    }
}
