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
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $pendingGodsons;

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

        $godson->getGodfather()->removePendingGodson($godson->getEmail());

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

    /**
     * Add pendingGodson
     *
     * @param $email
     * @return $this
     */
    public function addPendingGodson(string $email)
    {
        if (false === in_array($email, $this->pendingGodsons, true)) {
            $this->pendingGodsons[] = $email;
        }

        return $this;
    }

    /**
     * Remove pendingGodson
     *
     * @param $email
     * @return $this
     */
    public function removePendingGodson(string $email)
    {
        if (false !== $key = array_search(strtoupper($email), $this->pendingGodsons, true)) {
            unset($this->pendingGodsons[$key]);
            $this->pendingGodsons = array_values($this->pendingGodsons);
        }

        $pendingGodsons = array_combine($this->pendingGodsons, $this->pendingGodsons);
        if (isset($pendingGodsons[$email])) {
            unset($pendingGodsons[$email]);
        }
        $this->setPendingGodsons($pendingGodsons);

        return $this;
    }

    /**
     * Set pendingGodsons
     *
     * @param array $pendingGodsons
     *
     * @return User
     */
    public function setPendingGodsons(array $pendingGodsons)
    {
        $this->pendingGodsons = [];

        foreach ($pendingGodsons as $email) {
            $this->addPendingGodson($email);
        }

        return $this;
    }

    /**
     * Get pendingGodsons
     *
     * @return array
     */
    public function getPendingGodsons()
    {
       return $this->pendingGodsons;
    }

    /**
     * Has pendingGodson
     *
     * @return array
     */
    public function hasPendingGodson(string $email)
    {
        return in_array($email, $this->pendingGodsons);
    }
}
