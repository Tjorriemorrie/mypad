<?php

namespace My\PadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="My\PadBundle\Repository\AlbumRepository")
 * @ORM\Table(name="albums")
 * @ORM\HasLifecycleCallbacks
 */
class Album
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

	/** @ORM\Column(type="string", length=255, nullable=false) */
	private $title;

	/** @ORM\ManyToOne(targetEntity="Artist", inversedBy="albums") */
	private $artist;

	/** @ORM\OneToMany(targetEntity="Song", mappedBy="album") */
	private $songs;

	/** @ORM\Column(type="date", nullable=true) */
	private $releasedAt;

	/** @ORM\Column(type="smallint", nullable=true) */
	private $slots;

	/** @ORM\Column(type="smallint") */
	private $rated;

	/** @ORM\Column(type="float", nullable=true) */
	private $rating;

	////////////////////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Construct
	 */
	public function __construct()
	{
		$this->createdAt = new \DateTime();
		$this->rated = 0;
		$this->songs = new \Doctrine\Common\Collections\ArrayCollection();
	}


	/**
	 * @ORM\PreUpdate
	 */
	public function preUpdate()
	{
		$this->setModifiedAt(new \DateTime());
	}


	/**
	 * Update Ratings
	 */
	public function updateRatings()
	{
		$rated = 0;
		$rating = null;

		if ($this->getSongs()->count()) {
			$ratedSongs = $ratingSongs = array();
			foreach ($this->getSongs() as $albumSong) {
				$ratedSongs[] = $albumSong->getRated();
				$ratingSongs[] = (is_null($albumSong->getRating())) ? 0 : $albumSong->getRating();
			}
			$rated = array_sum($ratedSongs);
			$rating = ($rated < 1) ? null : (array_sum($ratingSongs) - ($this->getSongs()->count() / 2));
		}

		$this->setRated($rated);
		$this->setRating($rating);
	}


	/**
	 * Displays rating
	 */
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
     * Set releasedAt
     *
     * @param date $releasedAt
     */
    public function setReleasedAt($releasedAt)
    {
        $this->releasedAt = $releasedAt;
    }

    /**
     * Get releasedAt
     *
     * @return date 
     */
    public function getReleasedAt()
    {
        return $this->releasedAt;
    }

    /**
     * Set slots
     *
     * @param smallint $slots
     */
    public function setSlots($slots)
    {
        $this->slots = $slots;
    }

    /**
     * Get slots
     *
     * @return smallint 
     */
    public function getSlots()
    {
        return $this->slots;
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
     * @return My\PadBundle\Entity\Artist 
     */
    public function getArtist()
    {
        return $this->artist;
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
     * Get songs
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getSongs()
    {
        return $this->songs;
    }

    /**
     * Remove songs
     *
     * @param \My\PadBundle\Entity\Song $songs
     */
    public function removeSong(\My\PadBundle\Entity\Song $songs)
    {
        $this->songs->removeElement($songs);
    }
}