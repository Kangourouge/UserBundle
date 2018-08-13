<?php

namespace KRG\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="KRG\UserBundle\Repository\SponsorRepository")
 * @ORM\Table
 */
class Sponsor implements SponsorInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $email;

    /**
     * @ORM\ManyToOne(targetEntity="KRG\UserBundle\Entity\UserInterface", inversedBy="sponsors")
     * @ORM\JoinColumn(name="godfather_id", referencedColumnName="id")
     */
    protected $godfather;

    /**
     * @ORM\OneToOne(targetEntity="KRG\UserBundle\Entity\UserInterface")
     * @ORM\JoinColumn(name="godson_id", referencedColumnName="id", nullable=true)
     */
    protected $godson;

    public function __toString()
    {
        return null !== $this->getGodson() ? $this->getGodson()->getEmail() : $this->getEmail();
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
     * Set email
     *
     * @param string $email
     *
     * @return Sponsor
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set godfather
     *
     * @param UserInterface $godfather
     *
     * @return Sponsor
     */
    public function setGodfather(UserInterface $godfather = null)
    {
        $this->godfather = $godfather;

        $godfather->addSponsor($this);

        return $this;
    }

    /**
     * Get godfather
     *
     * @return UserInterface
     */
    public function getGodfather()
    {
        return $this->godfather;
    }

    /**
     * Set godson
     *
     * @param UserInterface $godson
     *
     * @return Sponsor
     */
    public function setGodson(UserInterface $godson = null)
    {
        $this->godson = $godson;

        return $this;
    }

    /**
     * Get godson
     *
     * @return UserInterface
     */
    public function getGodson()
    {
        return $this->godson;
    }
}
