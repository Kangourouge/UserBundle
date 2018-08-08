<?php

namespace KRG\UserBundle\Entity;

trait SponsorTrait
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $sponsorCode;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="godfather")
     */
    protected $godsons;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="godsons")
     * @ORM\JoinColumn(name="godfather_id", referencedColumnName="id")
     */
    protected $godfather;

    /**
     * @return string
     */
    public function getSponsorCode()
    {
        return $this->sponsorCode;
    }

    /**
     * @param string $sponsorCode
     */
    public function setSponsorCode($sponsorCode)
    {
        $this->sponsorCode = $sponsorCode;
    }

    /**
     * Add godson
     *
     * @param UserInterface $godson
     *
     * @return UserInterface
     */
    public function addGodson(UserInterface $godson)
    {
        $this->godsons[] = $godson;

        return $this;
    }

    /**
     * Remove godson
     *
     * @param UserInterface $godson
     */
    public function removeGodson(UserInterface $godson)
    {
        $this->godsons->removeElement($godson);
    }

    /**
     * Get godsons
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGodsons()
    {
        return $this->godsons;
    }

    /**
     * Set godfather
     *
     * @param UserInterface $godfather
     *
     * @return UserInterface
     */
    public function setGodfather(UserInterface $godfather = null)
    {
        $this->godfather = $godfather;
        $godfather->addGodson($this);

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
}
