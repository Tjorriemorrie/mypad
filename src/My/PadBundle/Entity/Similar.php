<?php

namespace My\PadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="My\PadBundle\Repository\SimilarRepository")
 * @ORM\Table(name="similars")
 * @ORM\HasLifecycleCallbacks
 */
class Similar
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

	/** @ORM\ManyToOne(targetEntity="Artist", inversedBy="similarsMain") */
	private $artistMain;

	/** @ORM\ManyToOne(targetEntity="Artist", inversedBy="similarsGood") */
	private $artistGood;

	/** @ORM\ManyToOne(targetEntity="Artist", inversedBy="similarsBad") */
	private $artistBad;

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

	/** @ORM\PreRemove */
	public function preRemove()
	{
//		$this->songa = $this->songb = $this->artista = $this->artistb = null;
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
     * Set artistMain
     *
     * @param My\PadBundle\Entity\Artist $artistMain
     */
    public function setArtistMain(\My\PadBundle\Entity\Artist $artistMain)
    {
        $this->artistMain = $artistMain;
    }

    /**
     * Get artistMain
     *
     * @return My\PadBundle\Entity\Artist 
     */
    public function getArtistMain()
    {
        return $this->artistMain;
    }

    /**
     * Set artistGood
     *
     * @param My\PadBundle\Entity\Artist $artistGood
     */
    public function setArtistGood(\My\PadBundle\Entity\Artist $artistGood)
    {
        $this->artistGood = $artistGood;
    }

    /**
     * Get artistGood
     *
     * @return My\PadBundle\Entity\Artist 
     */
    public function getArtistGood()
    {
        return $this->artistGood;
    }

    /**
     * Set artistBad
     *
     * @param My\PadBundle\Entity\Artist $artistBad
     */
    public function setArtistBad(\My\PadBundle\Entity\Artist $artistBad)
    {
        $this->artistBad = $artistBad;
    }

    /**
     * Get artistBad
     *
     * @return My\PadBundle\Entity\Artist 
     */
    public function getArtistBad()
    {
        return $this->artistBad;
    }
}