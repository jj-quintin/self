<?php

namespace Innova\SelfBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Session
 *
 * @ORM\Table("session")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Innova\SelfBundle\Repository\SessionRepository")
 */
class Session
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="passwd", type="string", length=255)
     */
    private $passwd;

    /**
    * @ORM\ManyToOne(targetEntity="Test", inversedBy="sessions")
    */
    protected $test;

    /**
     * @var string
     *
     * @ORM\Column(name="actif", type="boolean")
     */
    private $actif;

    /**
     * @var string
     *
     * @ORM\Column(name="globalScoreWording", type="string", length=255)
     */
    private $globalScoreWording = "Groupe cible conseillé";

    /**
     * @var string
     *
     * @ORM\Column(name="globalScoreShow", type="boolean")
     */
    private $globalScoreShow;

    /**
    * @ORM\OneToMany(targetEntity="Innova\SelfBundle\Entity\Trace", mappedBy="session", cascade={"remove"})
    */
    protected $traces;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->traces = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param  string  $name
     * @return Session
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * Set test
     *
     * @param  \Innova\SelfBundle\Entity\Test $test
     * @return Session
     */
    public function setTest(\Innova\SelfBundle\Entity\Test $test = null)
    {
        $this->test = $test;

        return $this;
    }

    /**
     * Get test
     *
     * @return \Innova\SelfBundle\Entity\Test
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     * Add traces
     *
     * @param  \Innova\SelfBundle\Entity\Session $traces
     * @return Session
     */
    public function addTrace(\Innova\SelfBundle\Entity\Session $traces)
    {
        $this->traces[] = $traces;

        return $this;
    }

    /**
     * Remove traces
     *
     * @param \Innova\SelfBundle\Entity\Session $traces
     */
    public function removeTrace(\Innova\SelfBundle\Entity\Session $traces)
    {
        $this->traces->removeElement($traces);
    }

    /**
     * Get traces
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTraces()
    {
        return $this->traces;
    }

    /**
     * Set actif
     *
     * @param  boolean $actif
     * @return Session
     */
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * Get actif
     *
     * @return string
     */
    public function getActif()
    {
        return $this->actif;
    }

    /**
     * Set passwd
     *
     * @param  string  $passwd
     * @return Session
     */
    public function setPasswd($passwd)
    {
        $this->passwd = $passwd;

        return $this;
    }

    /**
     * Get passwd
     *
     * @return string
     */
    public function getPasswd()
    {
        return $this->passwd;
    }

    /**
     * Set globalScoreWording
     *
     * @param string $globalScoreWording
     *
     * @return Session
     */
    public function setGlobalScoreWording($globalScoreWording)
    {
        $this->globalScoreWording = $globalScoreWording;

        return $this;
    }

    /**
     * Get globalScoreWording
     *
     * @return string
     */
    public function getGlobalScoreWording()
    {
        return $this->globalScoreWording;
    }

    /**
     * Set globalScoreShow
     *
     * @param boolean $globalScoreShow
     *
     * @return Session
     */
    public function setGlobalScoreShow($globalScoreShow)
    {
        $this->globalScoreShow = $globalScoreShow;

        return $this;
    }

    /**
     * Get globalScoreShow
     *
     * @return string
     */
    public function getGlobalScoreShow()
    {
        return $this->globalScoreShow;
    }
}
