<?php

namespace My\PadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="feelings")
 * @ORM\HasLifecycleCallbacks
 */
class Feeling
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

	/** ORM\ManyToOne(targetEntity="Song", inversedBy="feelings") */
	//private $song;

	/** @ORM\ManyToOne(targetEntity="Mood", inversedBy="feelingsGood") */
	private $moodGood;

	/** @ORM\ManyToOne(targetEntity="Mood", inversedBy="feelingsBad") */
	private $moodBad;

	////////////////////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////

	/** Construct */
	public function __construct()
	{
		$this->createdAt = new \DateTime();
	}

	/** @ORM\PreUpdate */
	public function preUpdate()
	{
		$this->setModifiedAt(new \DateTime());
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
     * Set song
     *
     * @param My\PadBundle\Entity\Song $song
     */
    public function setSong(\My\PadBundle\Entity\Song $song)
    {
        $this->song = $song;
    }

    /**
     * Get song
     *
     * @return My\PadBundle\Entity\Song
     */
    public function getSong()
    {
        return $this->song;
    }

    /**
     * Set moodGood
     *
     * @param My\PadBundle\Entity\Mood $moodGood
     */
    public function setMoodGood(\My\PadBundle\Entity\Mood $moodGood)
    {
        $this->moodGood = $moodGood;
    }

    /**
     * Get moodGood
     *
     * @return My\PadBundle\Entity\Mood
     */
    public function getMoodGood()
    {
        return $this->moodGood;
    }

    /**
     * Set moodBad
     *
     * @param My\PadBundle\Entity\Mood $moodBad
     */
    public function setMoodBad(\My\PadBundle\Entity\Mood $moodBad)
    {
        $this->moodBad = $moodBad;
    }

    /**
     * Get moodBad
     *
     * @return My\PadBundle\Entity\Mood
     */
    public function getMoodBad()
    {
        return $this->moodBad;
    }
}