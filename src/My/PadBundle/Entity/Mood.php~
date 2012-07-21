<?php

namespace My\PadBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="moods")
 * @ORM\HasLifecycleCallbacks
 */
class Mood
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

	/** @ORM\Column(type="string", length=20) */
	private $name;

	/** @ORM\OneToMany(targetEntity="Feeling", mappedBy="moodGood") */
	private $feelingsGood;

	/** @ORM\OneToMany(targetEntity="Feeling", mappedBy="moodBad") */
	private $feelingsBad;

	////////////////////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////

	/** Construct */
	public function __construct()
	{
		$this->createdAt = new \DateTime();
		$this->feelingsGood = new \Doctrine\Common\Collections\ArrayCollection();
		$this->feelingsBad = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add feelingsGood
     *
     * @param My\PadBundle\Entity\Feeling $feelingsGood
     */
    public function addFeelingsGood(\My\PadBundle\Entity\Feeling $feelingsGood)
    {
        $this->feelingsGood[] = $feelingsGood;
    }

    /**
     * Get feelingsGood
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getFeelingsGood()
    {
        return $this->feelingsGood;
    }

    /**
     * Add feelingsBad
     *
     * @param My\PadBundle\Entity\Feeling $feelingsBad
     */
    public function addFeelingsBad(\My\PadBundle\Entity\Feeling $feelingsBad)
    {
        $this->feelingsBad[] = $feelingsBad;
    }

    /**
     * Get feelingsBad
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getFeelingsBad()
    {
        return $this->feelingsBad;
    }

    /**
     * Add feelingsGood
     *
     * @param My\PadBundle\Entity\Feeling $feelingsGood
     */
    public function addFeeling(\My\PadBundle\Entity\Feeling $feelingsGood)
    {
        $this->feelingsGood[] = $feelingsGood;
    }
}