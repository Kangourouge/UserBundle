<?php

namespace KRG\UserBundle\Entity;


interface SponsorInterface
{
    public function getId();

    public function setEmail($email);

    public function getEmail();

    public function setGodfather(UserInterface $godfather = null);

    public function getGodfather();

    public function setGodson(UserInterface $godson = null);

    public function getGodson();
}
