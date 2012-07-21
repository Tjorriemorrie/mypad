<?php

namespace My\PadBundle\Entity;

/**
 * Last.fm API Model
 */
class Lastfm
{
	private $api_host;
	private $api_key;
	private $api_secret;
	public function __construct()
	{
		$this->api_host = 'http://ws.audioscrobbler.com/2.0/';
		$this->api_key = '2b532992c84242d372f5c0044d6883e5';
		$this->api_secret = '3c6688ac84deda063a697f5662a93eb0';
	}


	private function getUrl($params)
	{
		$sig = '';
		$url = $this->api_host . '?';
		$questionUsed = false;
		foreach ($params as $key => $value) {
			$sig .= $key . $value;
			if (!$questionUsed) $questionUsed = true;
			else $url .= '&';
			$url .= $key . '=' . urlencode($value);
		}
		$sig = md5($sig . $this->api_secret);
		$url .= '&api_sig=' . $sig;
		return $url;
	}


	/////////////////////////////////////////////////////////////////////////////////////
	// AUTH
	/////////////////////////////////////////////////////////////////////////////////////
	/**
	 * URL to get authenticated
	 */
	public function getAuthUrl()
	{
		return 'http://www.last.fm/api/auth/?api_key=' . $this->api_key;
	}


	/**
	 * Get authorisation session
	 */
	public function getSession($token)
	{
		$params = array(
			'api_key'	=> $this->api_key,
			'method'	=> 'auth.getSession',
			'token'		=> $token
		);
		$url = $this->getUrl($params);

		try {
			$xmlstr = file_get_contents($url);
			$xml = new \SimpleXMLElement($xmlstr);
			return (string) $xml->session->key;
		} catch (Exception $e) {
			if (strlen(trim($xmlstr)) === 32) return $xmlstr;
			throw $e;
		}
	}


	/**
	 * Generates Signature for POST
	 */
	private function generateSignature($postVars)
	{
		$str = '';
		foreach ($postVars as $key => $value) {
			$str .= $key . $value;
		}
		$str .= $this->api_secret;
		return md5($str);
	}


	/////////////////////////////////////////////////////////////////////////////////////
	// TRACK
	/////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Update Now Playing
	 */
	public function updateNowPlaying($s, $song)
	{
		//die(var_dump($s));
		//die(var_dump($song));
		if (is_null($song->getTitle()) || is_null($song->getArtist())) return array('result'=>'fail', 'msg'=>'No artist or track');

		$sig = '';
		$client = new \Zend_Http_Client();
		$client->setUri($this->api_host);
//		if (!is_null($song->getAlbum()))
//			$client->setParameterPost('album',	$song->getAlbum()->getTitle());		$sig .= 'album' . $song->getAlbum()->getTitle();
		$client->setParameterPost('api_key',	$this->api_key);					$sig .= 'api_key' . $this->api_key;
		$client->setParameterPost('artist',		$song->getArtist()->getName());		$sig .= 'artist' . $song->getArtist()->getName();
		$client->setParameterPost('method',		'track.updateNowPlaying');			$sig .= 'method' . 'track.updateNowPlaying';
		$client->setParameterPost('sk',			(string)$s);						$sig .= 'sk' . (string)$s;
		$client->setParameterPost('track',		$song->getTitle());					$sig .= 'track' . $song->getTitle();
//		if (!is_null($song->getTrack()))
//			$client->setParameterPost('trackNumber', $song->getTrack());			$sig .= 'trackNumber' . $song->getTrack();
		$client->setParameterPost('api_sig',	md5($sig . $this->api_secret));
		$client->setMethod(\Zend_Http_Client::POST);
		$client->setConfig(array(
    		'maxredirects'	=> 0,
    		'timeout'		=> 10,
		));


		for ($i=1; $i<=12; $i++) {
			try {
				$response = $client->request();
				if (!$response->isSuccessful()) return array('result'=>'fail', 'msg'=>$i . ': ' . $response->getBody(), 'sk'=>$s);
				$xml = new \SimpleXMLElement($response->getBody());

				$attributes = $xml->attributes();
				if ((string)$attributes['status'] !== 'ok') return array('result'=>'fail', 'msg'=>$i . ': ' . (string)$xml->error, 'sk'=>$s);
				else return array('result'=>'win', 'msg'=>'Now Playing Updated');//$xml);
			} catch (Exception $e) {
				$result = array('result'=>'fail', 'msg'=>$i . ': ' . $e->getMessage(), 'sk'=>$s);
			}
		}

		return $result;
	}

//	public function updateNowPlayingNew($s, $song)
//	{
//		if (is_null($song->getTitle()) || is_null($song->getArtist())) return array('result'=>'fail', 'msg'=>'No artist or track');
//
//		$postVars = array();
//		//if (!is_null($song->getAlbum())) $params['album'] = urlencode($song->album->title);
//		$postVars['api_key']	= $this->api_key;
//		$postVars['artist']		= $song->getArtist()->getName();
//		$postVars['method']		= 'track.updateNowPlaying';
//		$postVars['sk']			= $s;
//		$postVars['track']		= $song->getTitle();
//		//if (!is_null($song->track)) $params['trackNumber'] = urlencode($song->track);
//		$postVars['api_sig']	= $this->generateSignature($postVars);
//		die(var_dump($postVars));
//
//		$ch = curl_init($this->api_host);
//		curl_setopt($ch, CURLOPT_POST, 1);
//		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postVars));
//		curl_setopt($ch, CURLOPT_HEADER, 0);
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
//		$response = curl_exec($ch);
//		//die(var_dump($response));
//
//		$xml = new \SimpleXMLElement($response);
//		$attributes = $xml->attributes();
//		if ((string)$attributes['status'] !== 'ok') return array('result'=>'fail', 'msg'=>(string)$xml->error);
//		return array('result'=>'win', 'msg'=>$xml);
//	}


	/**
	 * Scrobbles Track
	 */
	public function scrobble($s, $song)
	{
		if (is_null($song->getTitle()) || is_null($song->getArtist())) return array('result'=>'fail', 'msg'=>'No artist or track');

		$sig = '';
		$client = new \Zend_Http_Client();
		$client->setUri($this->api_host);
		//if (!is_null($song->getAlbum())) $client->setParameterPost('album', $song->getAlbum()->getTitle());		$sig .= 'album' . $song->getAlbum()->getTitle();
		$client->setParameterPost('api_key',	$this->api_key);					$sig .= 'api_key' . $this->api_key;
		$client->setParameterPost('artist',		$song->getArtist()->getName());		$sig .= 'artist' . $song->getArtist()->getName();
		$client->setParameterPost('method',		'track.scrobble');					$sig .= 'method' . 'track.scrobble';
		$client->setParameterPost('sk',			$s);								$sig .= 'sk' . $s;
		$client->setParameterPost('timestamp',	gmdate(strtotime('-5 seconds')));	$sig .= 'timestamp' . gmdate(strtotime('-5 second'));
		$client->setParameterPost('track',		$song->getTitle());					$sig .= 'track' . $song->getTitle();
		//if (!is_null($song->getTrack())) $client->setParameterPost('trackNumber', $song->getTrack());			$sig .= 'trackNumber' . $song->getTrack();
		$client->setParameterPost('api_sig',	md5($sig . $this->api_secret));
		$client->setMethod(\Zend_Http_Client::POST);
		$client->setConfig(array(
    		'maxredirects'	=> 0,
    		'timeout'		=> 10,
		));

		for ($i=1; $i<=12; $i++) {
			try {
				$response = $client->request();
				if (!$response->isSuccessful()) return array('result'=>'fail', 'msg'=>$i . ': ' . $response->getBody());
				$xml = new \SimpleXMLElement($response->getBody());

				$attributes = $xml->attributes();
				if ((string)$attributes['status'] !== 'ok') return array('result'=>'fail', 'msg'=>$i . ': ' . (string)$xml->error);
				else return array('result'=>'win', 'msg'=>'+1');//Result: ' . var_dump($xml));
			} catch (Exception $e) {
				$result = array('result'=>'fail', 'msg'=>$i . ': ' . $e->getMessage());
			}
		}

		return $result;
	}


	/**
	 * (Un)Love Track
	 */
	public function setLove($s, $song)
	{
		if (is_null($song->getTitle()) || is_null($song->getArtist())) return array('result'=>'fail', 'msg'=>'');//No artist or track');
		if (is_null($song->getRating()) || $song->getRated() < 5) return array('result'=>'fail', 'msg'=>'');
		if ($song->getRating() >= 0.90) $method = 'track.love';
		else $method = 'track.unlove';

		$sig = '';
		$client = new \Zend_Http_Client();
		$client->setUri($this->api_host);
		$client->setParameterPost('api_key',	$this->api_key);					$sig .= 'api_key' . $this->api_key;
		$client->setParameterPost('artist',		$song->getArtist()->getName());		$sig .= 'artist' . $song->getArtist()->getName();
		$client->setParameterPost('method',		$method);							$sig .= 'method' . $method;
		$client->setParameterPost('sk',			$s);								$sig .= 'sk' . $s;
		$client->setParameterPost('track',		$song->getTitle());					$sig .= 'track' . $song->getTitle();
		$client->setParameterPost('api_sig',	md5($sig . $this->api_secret));
		$client->setMethod(\Zend_Http_Client::POST);
		$client->setConfig(array(
    		'maxredirects'	=> 0,
    		'timeout'		=> 10,
		));
		//if (!is_null($song->album)) $params['album'] = urlencode($song->album->title);
		//if (!is_null($song->track)) $params['trackNumber'] = urlencode($song->track);

		for ($i=1; $i<=12; $i++) {
			try {
				$response = $client->request();
				if (!$response->isSuccessful()) return array('result'=>'fail', 'msg'=>$i . ': ' . $response->getBody());
				$xml = new \SimpleXMLElement($response->getBody());

				$attributes = $xml->attributes();
				if ((string)$attributes['status'] !== 'ok') return array('result'=>'fail', 'msg'=>$i . ': ' . (string)$xml->error);
				else return array('result'=>'win', 'msg'=>(($song->getRating() >= 0.90) ? '<3' : ''));//$xml);
			} catch (Exception $e) {
				$result = array('result'=>'fail', 'msg'=>$i . ': ' . $e->getMessage());
			}
		}

		return $result;
	}


	/**
	 * Add Tags
	 */
	public function addTags($s, $song)
	{
		if (is_null($song->title) || is_null($song->artist)) return array('result'=>'fail', 'msg'=>'No artist or track');
		if (is_null($song->tag_main)) return array('result'=>'fail', 'msg'=>'Song not tagged yet.');
		else {
			$tags = $song->tag_main;
			if (!is_null($song->tag_sub)) $tags .= ',' . $song->tag_sub;
		}

		$sig = '';
		$client = new Zend_Http_Client();
		$client->setUri($this->api_host);
		$client->setParameterPost('api_key',	$this->api_key);					$sig .= 'api_key' . $this->api_key;
		$client->setParameterPost('artist',		$song->artist->name);				$sig .= 'artist' . $song->artist->name;
		$client->setParameterPost('method',		'track.addTags');					$sig .= 'method' . 'track.addTags';
		$client->setParameterPost('sk',			$s);								$sig .= 'sk' . $s;
		$client->setParameterPost('tags',		$tags);								$sig .= 'tags' . $tags;
		$client->setParameterPost('track',		$song->title);						$sig .= 'track' . $song->title;
		$client->setParameterPost('api_sig',	md5($sig . $this->api_secret));
		$client->setMethod(Zend_Http_Client::POST);
		$client->setConfig(array(
    		'maxredirects'	=> 0,
    		'timeout'		=> 10,
		));

		for ($i=1; $i<=12; $i++) {
			try {
				$response = $client->request();
				if (!$response->isSuccessful()) $result = array('result'=>'fail', 'msg'=>$i . ': ' . $response->getBody());
				$xml = new SimpleXMLElement($response->getBody());

				$attributes = $xml->attributes();
				if ((string)$attributes['status'] !== 'ok') return array('result'=>'fail', 'msg'=>$i . ': ' . (string)$xml->error);
				else return array('result'=>'win', 'msg'=>$xml);
			} catch (Exception $e) {
				$result = array('result'=>'fail', 'msg'=>$i . ': ' . $e->getMessage());
			}
		}

		return $result;
	}


	/**
	 * Get Track Top Tags
	 */
	public function getTopTags($s, $song)
	{
		if (is_null($song->title) || is_null($song->artist)) return array('result'=>'fail', 'msg'=>'No artist or track');

		$client = new Zend_Http_Client();
		$client->setUri($this->api_host);
		$client->setParameterGet(array(
		    'api_key'		=> $this->api_key,
			'artist'		=> $song->artist->name,
			'autocorrect'	=> 0,
			'method'		=> 'track.getTopTags',
			'track'			=> $song->title,
		));
		$client->setConfig(array(
    		'maxredirects'	=> 0,
    		'timeout'		=> 10,
		));

		for ($i=1; $i<=12; $i++) {
			try {
				$response = $client->request();
				if (!$response->isSuccessful()) $result = array('result'=>'fail', 'msg'=>$i . ': ' . $response->getBody());
				$xml = new SimpleXMLElement($response->getBody());

				$attributes = $xml->attributes();
				if ((string)$attributes['status'] !== 'ok') return array('result'=>'fail', 'msg'=>$i . ': ' . (string)$xml->error);
				else return array('result'=>'win', 'msg'=>$xml);
			} catch (Exception $e) {
				$result = array('result'=>'fail', 'msg'=>$i . ': ' . $e->getMessage());
			}
		}

		return $result;
	}


	/////////////////////////////////////////////////////////////////////////////////////
	// LAST.fm
	/////////////////////////////////////////////////////////////////////////////////////

	public function apiTrackSearch($value)
	{
		$value = str_replace(array('/', '_', '-'), ' ', $value);
		//echo '<h5>val = ' . $value . '</h5>';

		$url = $this->host . '?method=track.search';
		$url .= '&api_key=' . $this->apikey;
		$url .= '&track=' . urlencode($value);

		try {
			$xmlstr = file_get_contents($url);
			$xml = new SimpleXMLElement($xmlstr);
			//var_dump($xml->results->trackmatches);
			return $xml->results->trackmatches;
		} catch (Exception $e) {
			echo '<h4>url = ' . $url . '</h4>';
			var_dump($e);
		}
	}


	public function apiArtistSearch($value)
	{
		$value = str_replace(array('/', '_', '-'), ' ', $value);
		//echo '<h5>val = ' . $value . '</h5>';

		$url = $this->host . '?method=artist.search';
		$url .= '&api_key=' . $this->apikey;
		$url .= '&artist=' . urlencode($value);

		try {
			$xmlstr = file_get_contents($url);
			$xml = new SimpleXMLElement($xmlstr);
			//var_dump($xml->results->trackmatches);
			return $xml->results->artistmatches;
		} catch (Exception $e) {
			echo '<h4>url = ' . $url . '</h4>';
			var_dump($e);
		}
	}


	public function apiAlbumSearch($value)
	{
		$value = str_replace(array('/', '_', '-'), ' ', $value);
		//echo '<h5>val = ' . $value . '</h5>';

		$url = $this->host . '?method=album.search';
		$url .= '&api_key=' . $this->apikey;
		$url .= '&album=' . urlencode($value);

		try {
			$xmlstr = file_get_contents($url);
			$xml = new SimpleXMLElement($xmlstr);
			//var_dump($xml->results->trackmatches);
			return $xml->results->albummatches;
		} catch (Exception $e) {
			echo '<h4>url = ' . $url . '</h4>';
			var_dump($e);
		}
	}
}

