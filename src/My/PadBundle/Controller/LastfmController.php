<?php

namespace My\PadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use My\PadBundle\Entity\Lastfm;

class LastfmController extends Controller
{
    /**
     * @Route("/lastfm", name="lastfm")
     * @Template()
     */
    public function indexAction()
    {
    	$session = $this->get('session');
    	if ($session->has('lastfmToken') && $session->has('lastfmSk')) {
    		return array('link'=>true, 'lastfmToken'=>$session->get('lastfmToken'), 'lastfmSk'=>$session->get('lastfmSk'));
    	} else {
    		$lastfm = new Lastfm();
    		$url = $lastfm->getAuthUrl();
    		return array('link'=>false, 'url'=>$url);
    	}
    }


    /**
     * @Route("/lastfm/authorised", name="authorised")
     * @Template()
     */
    public function authorisedAction(Request $request)
    {
    	$token = $request->query->get('token', null);
    	if (strlen($token) !== 32) throw new \Exception('Unknown token: ' . $token);

    	$session = $this->get('session');
    	$session->set('lastfmToken', $token);

    	$lastfm = new Lastfm();
		$sessionKey = $lastfm->getSession($token);
		$session->set('lastfmSk', $sessionKey);

    	return $this->redirect($this->generateUrl('home'));
    }


    /**
     * @Route("/lastfm/nowplaying", name="lastfm_nowplaying")
     * @Template()
     */
    public function nowplayingAction()
    {
    	$session = $this->get('session');

    	if ($session->has('lastfmToken') && $session->has('lastfmSk')) $sk = $session->get('lastfmSk');
    	else return new Response('No Session Key');

    	$songId = $session->get('inplay');

    	$em = $this->getDoctrine()->getEntityManager();
    	$song = $em->getRepository('MyPadBundle:Song')->find($songId);
    	if (!$song) return new Response('No playing song: ' . $songId);

    	$lastfm = new Lastfm();
    	try {$response = $lastfm->updateNowPlaying($sk, $song);}
    	catch (\Exception $e) {$response = array('result'=>'fail', 'msg'=>'exception occurred: ' . $e->getMessage());}

    	if ($response['result'] == 'fail' && strpos($response['msg'], '12') === 0) $session->remove('lastfmSk');

    	return new Response($response['msg']);
    }


    /**
     * @Route("/lastfm/favsong", name="lastfm_favsong")
     * @Template()
     */
    public function favsongAction()
    {
    	//return new Response('<3');
    	$session = $this->get('session');

    	if ($session->has('lastfmToken') && $session->has('lastfmSk')) $sk = $session->get('lastfmSk');
    	else return new Response('');//No Session Key');

    	$songId = $session->get('inplay');

    	$em = $this->getDoctrine()->getEntityManager();
    	$song = $em->getRepository('MyPadBundle:Song')->find($songId);
    	if (!$song) return new Response('');//No song to play: ' . $songId);

    	$lastfm = new Lastfm();
    	try {$response = $lastfm->setLove($sk, $song);}
    	catch (\Exception $e) {$response = array('result'=>'fail', 'msg'=>'Error with webservice!');}

    	//if ($response['result'] == 'fail' && strpos($response['msg'], '12') === 0) $session->remove('lastfmSk');

    	return new Response($response['msg']);
    }


    /**
     * @Route("/lastfm/scrobble", name="lastfm_scrobble")
     */
    public function scrobbleAction()
    {
       	// retrieve current song from session
    	$session = $this->get('session');
    	$songId = $session->get('inplay');

    	$em = $this->getDoctrine()->getEntityManager();
    	$song = $em->getRepository('MyPadBundle:Song')->find($songId);
    	if (!$song) throw $this->createNotFoundException('No song to play');

		if ($session->has('lastfmToken') && $session->has('lastfmSk')) {
			$sk = $session->get('lastfmSk');
			$lastfm = new \My\PadBundle\Entity\Lastfm();
			try {$response = $lastfm->scrobble($sk, $song);}
			catch (\Exception $e) {$response = array('result'=>'fail', 'msg'=>'Error with webservice!');}
		} else $response = array('result'=>'fail', 'msg'=>'No Session Key');

		return new Response($response['msg']);
    }
}