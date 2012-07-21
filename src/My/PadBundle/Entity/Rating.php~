<?php

namespace My\PadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="My\PadBundle\Repository\RatingRepository")
 * @ORM\Table(name="ratings")
 * @ORM\HasLifecycleCallbacks
 */
class Rating
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

	/** @ORM\ManyToOne(targetEntity="Song", inversedBy="winners") */
	private $winner;

	/** @ORM\ManyToOne(targetEntity="Song", inversedBy="losers") */
	private $loser;

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
     * Set winner
     *
     * @param My\PadBundle\Entity\Song $winner
     */
    public function setWinner(\My\PadBundle\Entity\Song $winner)
    {
        $this->winner = $winner;
    }

    /**
     * Get winner
     *
     * @return My\PadBundle\Entity\Song
     */
    public function getWinner()
    {
        return $this->winner;
    }

    /**
     * Set loser
     *
     * @param My\PadBundle\Entity\Song $loser
     */
    public function setLoser(\My\PadBundle\Entity\Song $loser)
    {
        $this->loser = $loser;
    }

    /**
     * Get loser
     *
     * @return My\PadBundle\Entity\Song
     */
    public function getLoser()
    {
        return $this->loser;
    }
}