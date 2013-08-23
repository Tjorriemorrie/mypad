<?php

namespace My\PadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use My\PadBundle\Entity\Artist;
use My\PadBundle\Entity\Album;

/**
 * @ORM\Entity(repositoryClass="My\PadBundle\Repository\SongRepository")
 * @ORM\Table(name="songs")
 * @ORM\HasLifecycleCallbacks
 */
class Song
{
	/**
	 * @ORM\Id @ORM\GeneratedValue(strategy="IDENTITY")
	 * @ORM\Column(type="bigint")
	 */
	private $id;

	/** @ORM\Column(type="datetime") */
	private $createdAt;

	/** @ORM\Column(type="datetime", nullable=true) */
	private $modifiedAt;

	////////////////////
	// FILE INFO
	////////////////////

	/** @ORM\Column(type="string", length=255, unique=true) */
	private $path;

	/** @ORM\Column(type="string", length=3) */
	private $codec;

	/** @ORM\Column(type="smallint") */
	private $status;
	const STATUS_DEACTIVE	= -1;
	const STATUS_NEW		= 0;
	const STATUS_ACTIVE		= 1;

	/** @ORM\Column(type="smallint") */
	private $playcount;

	/** @ORM\Column(type="datetime", nullable=true) */
	private $playedAt;

	////////////////////
	// TRACK INFO
	////////////////////

	/** @ORM\Column(type="smallint", nullable=true) */
	private $track;

	/** @ORM\Column(type="string", length=255, nullable=true) */
	private $title;

	/** @ORM\ManyToOne(targetEntity="Artist", inversedBy="songs") */
	private $artist;

	/** @ORM\ManyToOne(targetEntity="Album", inversedBy="songs") */
	private $album;

	////////////////////
	// RATINGS
	////////////////////

	/** @ORM\OneToMany(targetEntity="Rating", mappedBy="winner", cascade={"remove"}, orphanRemoval=true) */
	private $winners;

	/** @ORM\OneToMany(targetEntity="Rating", mappedBy="loser", cascade={"remove"}, orphanRemoval=true) */
	private $losers;

	/** @ORM\Column(type="float", nullable=true) */
	private $rating;

	/** @ORM\Column(type="smallint") */
	private $rated;

	/** @ORM\Column(type="datetime", nullable=true) */
	private $ratedAt;

	/** @ORM\Column(type="float") */
	private $priority;

	////////////////////
	// MOOD
	////////////////////

	/** aORM\OneToMany(targetEntity="Feeling", mappedBy="song") */
	//private $feelings;

	////////////////////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////

	/** Construct */
	public function __construct()
	{
		$this->createdAt = new \DateTime();

		$this->status = self::STATUS_NEW;
		$this->playcount = 0;

		$this->rated	= 0;
		$this->priority	= 1;

		$this->winners	= new \Doctrine\Common\Collections\ArrayCollection();
		$this->losers	= new \Doctrine\Common\Collections\ArrayCollection();

		//$this->feelings	= new \Doctrine\Common\Collections\ArrayCollection();
	}

	/** @ORM\PreUpdate */
	public function preUpdate()
	{
		$this->setModifiedAt(new \DateTime());
	}


	/** Post play */
	public function postPlay($maxPlaycount)
	{
		$this->setPlaycount(($this->getPlaycount() + 1));
		$this->setPlayedAt(new \DateTime());
		$this->updatePriority($maxPlaycount);

		//$this->updateId3();

		$this->renameFile();
	}


	/** Update Ratings */
	public function updateRatings($maxPlaycount, $inc)
	{
		$rated = $this->getWinners()->count() + $this->getLosers()->count() + 1;
		if ($rated === 0) {
			$this->setRated(0);
			$this->setRating(null);
			$this->setRatedAt(null);
		} else {
			$this->setRated($rated);
			$this->setRating(($this->winners->count() + $inc) / $rated);
			$this->setRatedAt(new \DateTime());
		}
		$this->updatePriority($maxPlaycount);

		if (!is_null($this->getArtist())) $this->getArtist()->updateRatings();
		if (!is_null($this->getAlbum())) $this->getAlbum()->updateRatings();
	}


	/** Update Priority */
	public function updatePriority($maxPlaycount)
	{
		if ($this->getPlaycount() == 0 || is_null($this->getTitle()) || is_null($this->getArtist())) {
			$this->setPriority(1);
		} else {
			$playcountWeighed = $this->getPlaycount() / $maxPlaycount;

			$rating = $this->getRating();
			if (is_null($rating)) $rating = 0;

            $priority = abs($rating) - abs($playcountWeighed * 7/8) - abs((1 - $rating) * 7/8);
            $priority = max(-1, $priority);
            //$priority = abs($rating * 3 / 4) - abs($playcountWeighed * 1 / 4);
            //$priority = abs($rating * 3/6) - abs($playcountWeighed * 1/6) - abs((1 - $rating) * 2/6);
			$this->setPriority($priority);
		}
	}

	/** Loads id3v2 tag info */
	public function loadId3($em)
	{
		$id3v1 = $id3v2 = null;
		try {$id3v1 = new \Zend_Media_Id3v1(AUDIO_PATH . '/' . $this->getPath());}
		catch (\Exception $e) {/* ignore */}
		try {$id3v2 = new \Zend_Media_Id3v2(AUDIO_PATH . '/' . $this->getPath());}
		catch (\Exception $e) {/* ignore */}

		// if no id3 v1 or v2 then return;
		if (is_null($id3v1) && is_null($id3v2)) return $this;

		// track
		if (is_null($this->getTrack())) {
			if (!is_null($id3v1) && $id3v1->getTrack() != '') $this->setTrack($id3v1->getTrack());
			elseif (!is_null($id3v2) && $id3v2->trck->text != '') $this->setTrack($id3v2->trck->text);
		}

		// title
		if (is_null($this->getTitle())) {
			if (!is_null($id3v1) && $id3v1->getTitle() != '') $this->setTitle('id3v1 success'); //$id3v1->getTitle());
			elseif (!is_null($id3v2) && $id3v2->tit2->text != '') $this->setTitle($id3v2->tit2->text);
		}

		// artist
		if (is_null($this->getArtist())) {
			if (!is_null($id3v1) && $id3v1->getArtist() != '') $artistName = $id3v1->getArtist();
			elseif (!is_null($id3v2) && $id3v2->tpe1->text != '') $artistName = $id3v2->tpe1->text;
			if (isset($artistName)) {
				$artist = $em->getRepository('MyPadBundle:Artist')->findOneByName($artistName);
				if (!$artist) {
					$artist = new \My\PadBundle\Entity\Artist();
					$em->persist($artist);
					$artist->setName($artistName);
				}
				$this->setArtist($artist);
			}
		}

		// album
		if (is_null($this->getAlbum())) {
			if (!is_null($id3v1) && $id3v1->getAlbum() != '') $albumTitle = $id3v1->getAlbum();
			elseif (!is_null($id3v2) && $id3v2->talb->text != '') $albumTitle = $id3v2->talb->text;
			if (isset($albumTitle)) {
				$album = false;
				if (!is_null($this->getArtist())) $album = $em->getRepository('MyPadBundle:Album')->findOneBy(array('title'=>$albumTitle, 'artist'=>$this->getArtist()->getId()));
				if (!$album) $album = $em->getRepository('MyPadBundle:Album')->findOneByTitle($albumTitle);
				if (!$album) {
					$album = new \My\PadBundle\Entity\Album();
					$em->persist($album);
					$album->setTitle($albumTitle);
				}
				$this->setAlbum($album);
			}
		}

		// Year
		if (!is_null($this->getAlbum()) && is_null($this->getAlbum()->getReleasedAt())) {
			if (!is_null($id3v1) && $id3v1->getYear() != '') $year = $id3v1->getYear();
			elseif (!is_null($id3v2) && $id3v2->tyer->text != '') $year = $id3v2->tyer->text;
			if (isset($year)) {
				$this->getAlbum()->setReleasedAt(new \DateTime(date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, $year))));
			}
		}

		return $this;
	}

	/** Updates id3v2 tag */
	public function updateId3()
	{
		try {$id3v1 = new \Zend_Media_Id3v1(AUDIO_PATH . '/' . $this->getPath());}
		catch (\Exception $e) {$id3v1 = new \Zend_Media_Id3v1();}
		try {$id3v2 = new \Zend_Media_Id3v2(AUDIO_PATH . '/' . $this->getPath());}
		catch (\Exception $e) {$id3v2 = new \Zend_Media_Id3v2();}

		// track
		if (!is_null($this->getTrack())) {
			$id3v1->setTrack($this->getTrack());
			$id3v2->trck->text = $this->getTrack();
		}

		// title
		if (!is_null($this->getTitle())) {
			$id3v1->setTitle($this->getTitle());
			$id3v2->tit2->text = $this->getTitle();
		}

		// artist
		if (!is_null($this->getArtist())) {
			$id3v1->setArtist($this->getArtist()->getName());
			$id3v2->tpe1->text = $this->getArtist()->getName();
		}

		// album (Talb) (Tpos number of set e.g. 1/2)
		if (!is_null($this->getAlbum())) {
			$id3v1->setAlbum($this->getAlbum()->getTitle());
			$id3v2->talb->text = $this->getAlbum()->getTitle();
		}

		// tyer = year (Tory = original release year)
		if (!is_null($this->getAlbum()) && !is_null($this->getAlbum()->getReleasedAt())) {
			$id3v1->setYear($this->getAlbum()->getReleasedAt()->format('Y'));
			$id3v2->tyer->text = $this->getAlbum()->getReleasedAt()->format('Y');
		}

		$id3v1->write(AUDIO_PATH . '/' . $this->getPath());
		$id3v2->write(AUDIO_PATH . '/' . $this->getPath());
		return $this;
	}

	/** Renames the song after play */
	public function renameFile()
	{
		$reservedCharacters = array('/', '\\', '?', '%', '*', ':', '|', '"', '<', '>', '.');
        // add non-working
        $reservedCharacters[] = '#';

		// TITLE
		if (is_null($this->getTitle())) {
			$path = $this->getPath();
		} else {
			$songName = str_replace($reservedCharacters, '', $this->getTitle());
			if (!is_null($this->getTrack())) $songName = $this->getTrack() . ' ' . $songName;
			$songName .= '.' . $this->getCodec();

			// ARTIST
			if (is_null($this->getArtist())) {
				$path = $songName;
			}
			// add artist dir
			else {
				$artistName = str_replace($reservedCharacters, '', $this->getArtist()->getName());
				if (!is_dir(AUDIO_PATH . '/' . $artistName)) mkdir(AUDIO_PATH . '/' . $artistName);

				// ALBUM
				if (is_null($this->getAlbum())) {
					$path = implode('/', array($artistName, $songName));
				}
				// add album dir
				else {
					$albumTitle = str_replace($reservedCharacters, '', $this->getAlbum()->getTitle());
					if (!is_null($this->getAlbum()->getReleasedAt())) $albumTitle .= ' [' . $this->getAlbum()->getReleasedAt()->format('Y') . ']';
					if (!is_dir(AUDIO_PATH . '/' . $artistName . '/' . $albumTitle)) mkdir(AUDIO_PATH . '/' . $artistName . '/' . $albumTitle);

					$path = implode('/', array($artistName, $albumTitle, $songName));
				}
			}
		}

		// rename action
		// if not the same file
		if ($path !== $this->getPath()) {
			// ignore if a file exists
			if (!file_exists(AUDIO_PATH . '/' . $path)) {
				rename(AUDIO_PATH . '/' . $this->getPath(), AUDIO_PATH . '/' . $path) or die('could not rename file');
				$this->setPath($path);
			}
		}
//		try {
//			$exist = $em->getRepository('\My\Entity\this')->getHash($path);
//			// if exist (then duplicate) then remove (if not the same this)
//			if ($exist->id != $this->id) {
//				unlink(PUBLIC_PATH . $this->path);
//
//				// remove this
//				$em->getRepository('\My\Entity\this')->removethis($this);
//				try {
//					$em->flush();
//				} catch (Exception $e) {
//					echo '<h1>ERr</h1>';
//					echo $e->getMessage();
//					echo 'thisid = ' . $this->id;
//					die();
//				}
//
//			}
//		} catch (Exception $e) {
//			// does not exist, now rename/move file
//			rename(PUBLIC_PATH . $this->path, PUBLIC_PATH . $path);
//			// save new path and filename
//			$this->hash = md5($path);
//			$this->path = $path;
//			$em->persist($this);
//		}
	}

	/** Displays rating */
	public function getDisplayRating()
	{
		if (is_null($this->getRating())) return '&mdash;';
		return round($this->getRating() * 100) . '%';
	}

	/** Displays priority */
	public function getDisplayPriority()
	{
		if (is_null($this->getPriority()) || $this->getPlaycount() == 0) return '&mdash;';

		if ($this->getPriority() > 0.05) $roundDecimals = 0;
		elseif ($this->getPriority() > 0.02) $roundDecimals = 1;
		else $roundDecimals = 2;

		return round($this->getPriority() * 100, $roundDecimals) . '%';
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Get id
     *
     * @return bigint
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set modifiedAt
     *
     * @param datetime $modifiedAt
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
    }

    /**
     * Get modifiedAt
     *
     * @return datetime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * Set path
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set codec
     *
     * @param string $codec
     */
    public function setCodec($codec)
    {
        $this->codec = $codec;
    }

    /**
     * Get codec
     *
     * @return string
     */
    public function getCodec()
    {
        return $this->codec;
    }

    /**
     * Set status
     *
     * @param smallint $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return smallint
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set playcount
     *
     * @param smallint $playcount
     */
    public function setPlaycount($playcount)
    {
        $this->playcount = $playcount;
    }

    /**
     * Get playcount
     *
     * @return smallint
     */
    public function getPlaycount()
    {
        return $this->playcount;
    }

    /**
     * Set playedAt
     *
     * @param datetime $playedAt
     */
    public function setPlayedAt($playedAt)
    {
        $this->playedAt = $playedAt;
    }

    /**
     * Get playedAt
     *
     * @return datetime
     */
    public function getPlayedAt()
    {
        return $this->playedAt;
    }

    /**
     * Set track
     *
     * @param smallint $track
     */
    public function setTrack($track)
    {
        $this->track = $track;
    }

    /**
     * Get track
     *
     * @return smallint
     */
    public function getTrack()
    {
        return $this->track;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set rating
     *
     * @param float $rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    /**
     * Get rating
     *
     * @return float
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set rated
     *
     * @param smallint $rated
     */
    public function setRated($rated)
    {
        $this->rated = $rated;
    }

    /**
     * Get rated
     *
     * @return smallint
     */
    public function getRated()
    {
        return $this->rated;
    }

    /**
     * Set ratedAt
     *
     * @param datetime $ratedAt
     */
    public function setRatedAt($ratedAt)
    {
        $this->ratedAt = $ratedAt;
    }

    /**
     * Get ratedAt
     *
     * @return \DateTime
     */
    public function getRatedAt()
    {
        return $this->ratedAt;
    }

    /**
     * Set priority
     *
     * @param float $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * Get priority
     *
     * @return float
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set artist
     *
     * @param My\PadBundle\Entity\Artist $artist
     */
    public function setArtist(\My\PadBundle\Entity\Artist $artist)
    {
        $this->artist = $artist;
    }

    /**
     * Get artist
     *
     * @return Artist
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * Set album
     *
     * @param My\PadBundle\Entity\Album $album
     */
    public function setAlbum(\My\PadBundle\Entity\Album $album)
    {
        $this->album = $album;
    }

    /**
     * Get album
     *
     * @return Album
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * Add winners
     *
     * @param My\PadBundle\Entity\Rating $winners
     */
    public function addWinners(\My\PadBundle\Entity\Rating $winners)
    {
        $this->winners[] = $winners;
    }

    /**
     * Get winners
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getWinners()
    {
        return $this->winners;
    }

    /**
     * Add losers
     *
     * @param My\PadBundle\Entity\Rating $losers
     */
    public function addLosers(\My\PadBundle\Entity\Rating $losers)
    {
        $this->losers[] = $losers;
    }

    /**
     * Get losers
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getLosers()
    {
        return $this->losers;
    }

    /**
     * Add winners
     *
     * @param My\PadBundle\Entity\Rating $winners
     */
    public function addRating(\My\PadBundle\Entity\Rating $winners)
    {
        $this->winners[] = $winners;
    }

    /**
     * Add winners
     *
     * @param \My\PadBundle\Entity\Rating $winners
     * @return Song
     */
    public function addWinner(\My\PadBundle\Entity\Rating $winners)
    {
        $this->winners[] = $winners;

        return $this;
    }

    /**
     * Remove winners
     *
     * @param \My\PadBundle\Entity\Rating $winners
     */
    public function removeWinner(\My\PadBundle\Entity\Rating $winners)
    {
        $this->winners->removeElement($winners);
    }

    /**
     * Add losers
     *
     * @param \My\PadBundle\Entity\Rating $losers
     * @return Song
     */
    public function addLoser(\My\PadBundle\Entity\Rating $losers)
    {
        $this->losers[] = $losers;

        return $this;
    }

    /**
     * Remove losers
     *
     * @param \My\PadBundle\Entity\Rating $losers
     */
    public function removeLoser(\My\PadBundle\Entity\Rating $losers)
    {
        $this->losers->removeElement($losers);
    }
}
