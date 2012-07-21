<?php

namespace My\PadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="My\PadBundle\Repository\ArtistRepository")
 * @ORM\Table(name="artists")
 * @ORM\HasLifecycleCallbacks
 */
class Artist
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

	/** @ORM\Column(type="string", length=255, unique=true, nullable=false) */
	private $name;

	/** @ORM\OneToMany(targetEntity="Album", mappedBy="artist") */
	private $albums;

	/** @ORM\OneToMany(targetEntity="Song", mappedBy="artist") */
	private $songs;

	/** @ORM\Column(type="smallint") */
	private $rated;

	/** @ORM\Column(type="float", nullable=true) */
	private $rating;

	////////////////////
	// SIMILARS
	////////////////////

	/** @ORM\OneToMany(targetEntity="Similar", mappedBy="artistMain", cascade={"remove"}, orphanRemoval=true) */
	private $similarsMain;

	/** @ORM\OneToMany(targetEntity="Similar", mappedBy="artistGood", cascade={"remove"}, orphanRemoval=true) */
	private $similarsGood;

	/** @ORM\OneToMany(targetEntity="Similar", mappedBy="artistBad", cascade={"remove"}, orphanRemoval=true) */
	private $similarsBad;

	/** @ORM\Column(type="smallint") */
	private $similared;

	/** @ORM\Column(type="datetime", nullable=true) */
	private $similarAt;

	////////////////////
	// NEW
	////////////////////
	/**
	 * @ORM\Column(type="smallint")
	 */
	private $fullCounter;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $fullAt;


	////////////////////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////

	/** Construct */
	public function __construct()
	{
		$this->createdAt = new \DateTime();

		$this->rated = 0;
		$this->similared = 0;
		$this->fullCounter = 0;

		$this->albums = new \Doctrine\Common\Collections\ArrayCollection();
		$this->songs = new \Doctrine\Common\Collections\ArrayCollection();

		$this->similarsMain = new \Doctrine\Common\Collections\ArrayCollection();
		$this->similarsGood = new \Doctrine\Common\Collections\ArrayCollection();
		$this->similarsBad = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/** @ORM\PreUpdate */
	public function preUpdate()
	{
		$this->setModifiedAt(new \DateTime());

		$this->setSimilared($this->getSimilarsMain()->count());
	}

	/** Update Ratings */
	public function updateRatings()
	{
		$rated = 0;
		$rating = null;

		if ($this->getSongs()->count()) {
			$ratedSongs = $ratingSongs = array();
			foreach ($this->getSongs() as $artistSong) {
				$ratedSongs[] = $artistSong->getRated();
				$ratingSongs[] = (is_null($artistSong->getRating())) ? 0 : $artistSong->getRating();
			}
			$rated = array_sum($ratedSongs);
			$rating = ($rated < 1) ? null : (array_sum($ratingSongs) - ($this->getSongs()->count() / 2));
		}

		$this->setRated($rated);
		$this->setRating($rating);
	}

	/** Displays rating */
	public function getDisplayRating()
	{
		if (is_null($this->getRating())) return '&mdash;';
		return round($this->getRating() * 100) . '%';
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Set similarAt
     *
     * @param datetime $similarAt
     */
    public function setSimilarAt($similarAt)
    {
        $this->similarAt = $similarAt;
    }

    /**
     * Get similarAt
     *
     * @return datetime
     */
    public function getSimilarAt()
    {
        return $this->similarAt;
    }

    /**
     * Add albums
     *
     * @param My\PadBundle\Entity\Album $albums
     */
    public function addAlbums(\My\PadBundle\Entity\Album $albums)
    {
        $this->albums[] = $albums;
    }

    /**
     * Get albums
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getAlbums()
    {
        return $this->albums;
    }

    /**
     * Add songs
     *
     * @param My\PadBundle\Entity\Song $songs
     */
    public function addSongs(\My\PadBundle\Entity\Song $songs)
    {
        $this->songs[] = $songs;
    }

    /**
     * Get songs
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getSongs()
    {
        return $this->songs;
    }

    /**
     * Add similarsa
     *
     * @param My\PadBundle\Entity\Similar $similarsa
     */
    public function addSimilarsa(\My\PadBundle\Entity\Similar $similarsa)
    {
        $this->similarsa[] = $similarsa;
    }

    /**
     * Get similarsa
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getSimilarsa()
    {
        return $this->similarsa;
    }

    /**
     * Add similarsb
     *
     * @param My\PadBundle\Entity\Similar $similarsb
     */
    public function addSimilarsb(\My\PadBundle\Entity\Similar $similarsb)
    {
        $this->similarsb[] = $similarsb;
    }

    /**
     * Get similarsb
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getSimilarsb()
    {
        return $this->similarsb;
    }

    /**
     * Set similared
     *
     * @param smallint $similared
     */
    public function setSimilared($similared)
    {
        $this->similared = $similared;
    }

    /**
     * Get similared
     *
     * @return smallint
     */
    public function getSimilared()
    {
        return $this->similared;
    }

    /**
     * Add similarsMain
     *
     * @param My\PadBundle\Entity\Similar $similarsMain
     */
    public function addSimilarsMain(\My\PadBundle\Entity\Similar $similarsMain)
    {
        $this->similarsMain[] = $similarsMain;
    }

    /**
     * Get similarsMain
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getSimilarsMain()
    {
        return $this->similarsMain;
    }

    /**
     * Add similarsGood
     *
     * @param My\PadBundle\Entity\Similar $similarsGood
     */
    public function addSimilarsGood(\My\PadBundle\Entity\Similar $similarsGood)
    {
        $this->similarsGood[] = $similarsGood;
    }

    /**
     * Get similarsGood
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getSimilarsGood()
    {
        return $this->similarsGood;
    }

    /**
     * Add similarsBad
     *
     * @param My\PadBundle\Entity\Similar $similarsBad
     */
    public function addSimilarsBad(\My\PadBundle\Entity\Similar $similarsBad)
    {
        $this->similarsBad[] = $similarsBad;
    }

    /**
     * Get similarsBad
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getSimilarsBad()
    {
        return $this->similarsBad;
    }

    /**
     * Add albums
     *
     * @param My\PadBundle\Entity\Album $albums
     */
    public function addAlbum(\My\PadBundle\Entity\Album $albums)
    {
        $this->albums[] = $albums;
    }

    /**
     * Add songs
     *
     * @param My\PadBundle\Entity\Song $songs
     */
    public function addSong(\My\PadBundle\Entity\Song $songs)
    {
        $this->songs[] = $songs;
    }

    /**
     * Add similarsMain
     *
     * @param My\PadBundle\Entity\Similar $similarsMain
     */
    public function addSimilar(\My\PadBundle\Entity\Similar $similarsMain)
    {
        $this->similarsMain[] = $similarsMain;
    }

    /**
     * Set fullCounter
     *
     * @param smallint $fullCounter
     */
    public function setFullCounter($fullCounter)
    {
        $this->fullCounter = $fullCounter;
    }

    /**
     * Get fullCounter
     *
     * @return smallint 
     */
    public function getFullCounter()
    {
        return $this->fullCounter;
    }

    /**
     * Set fullAt
     *
     * @param datetime $fullAt
     */
    public function setFullAt($fullAt)
    {
        $this->fullAt = $fullAt;
    }

    /**
     * Get fullAt
     *
     * @return datetime 
     */
    public function getFullAt()
    {
        return $this->fullAt;
    }
}