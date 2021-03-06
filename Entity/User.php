<?php

namespace KRG\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use KRG\UserBundle\Annotation\PrivateData;
use KRG\UserBundle\Doctrine\DBAL\GenderEnum;
use KRG\UserBundle\Util\Canonicalizer;
use KRG\UserBundle\Validator\Constraint\EmailUnique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass(repositoryClass="KRG\UserBundle\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @ORM\Column(type="gender_enum", nullable=true)
     * @var string
     */
    protected $gender;

    /**
     * @PrivateData(domain="kangourouge.com")
     * @ORM\Column(type="string", unique=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $emailCanonical;

    /**
     * @EmailUnique()
     * @ORM\Column(type="string", nullable=true)
     */
    protected $emailAlteration;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $emailBackup;

    /**
     * @PrivateData(replaceWith="John")
     * @ORM\Column(type="string", nullable=true)
     */
    protected $firstname;

    /**
     * @PrivateData(replaceWith="Doe")
     * @ORM\Column(type="string", nullable=true)
     */
    protected $lastname;

    /**
     * The salt to use for hashing.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $salt;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @var string
     * @Assert\Length(
     *      min = 8,
     *      minMessage = "user.password.minMessage"
     * )
     * @Assert\Regex(
     *     pattern = "#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W)#",
     *     message = "user.password.regex"
     * )
     */
    protected $plainPassword;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastLogin;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $confirmationToken;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $invitationToken;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $cancelAlterationToken;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $terms;

    /**
     * @ORM\Column(type="json_array")
     */
    protected $roles;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $sponsorCode;

    /**
     * @ORM\OneToMany(targetEntity="KRG\UserBundle\Entity\SponsorInterface", mappedBy="godfather", cascade={"all"})
     */
    protected $sponsors;

    public function __construct()
    {
        $this->enabled = false;
        $this->terms = false;
        $this->roles = [];
        $this->sponsors = new ArrayCollection();
    }

    public function __toString()
    {
        return sprintf('%s <%s>', $this->getName(), $this->getEmail());
    }

    /**
     * @return string
     */
    public function getName()
    {
        $gender = $this->gender ? ($this->gender === GenderEnum::MALE ? 'M.' : 'Mme.') : '';

        return ucwords(trim(sprintf('%s %s %s', $gender, $this->firstname, $this->lastname)));
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->password,
            $this->salt,
            $this->email,
            $this->emailCanonical,
            $this->enabled
        ]);
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->password,
            $this->salt,
            $this->email,
            $this->emailCanonical,
            $this->enabled
        ) = unserialize($serialized);
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
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
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->getEmail();
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        $this->setEmailCanonical(Canonicalizer::canonicalize($email));

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
     * Get email canonical
     *
     * @return string
     */
    public function getEmailCanonical()
    {
        return $this->emailCanonical;
    }

    /**
     * Set email
     *
     * @param string $emailCanonical
     *
     * @return User
     */
    public function setEmailCanonical($emailCanonical)
    {
        $this->emailCanonical = $emailCanonical;

        return $this;
    }

    /**
     * Get email alteration
     *
     * @return string
     */
    public function getEmailAlteration()
    {
        return $this->emailAlteration;
    }

    /**
     * Set email alteration
     *
     * @param string $emailAlteration
     *
     * @return User
     */
    public function setEmailAlteration($emailAlteration)
    {
        $this->emailAlteration = $emailAlteration;

        return $this;
    }

    /**
     * Get email backup
     *
     * @return string
     */
    public function getEmailBackup()
    {
        return $this->emailBackup;
    }

    /**
     * Set email backup
     *
     * @param string $emailBackup
     *
     * @return User
     */
    public function setEmailBackup($emailBackup)
    {
        $this->emailBackup = $emailBackup;

        return $this;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set lastLogin
     *
     * @param \DateTime $lastLogin
     *
     * @return User
     */
    public function setLastLogin(\DateTime $lastLogin = null)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set confirmationToken
     *
     * @param string $confirmationToken
     *
     * @return User
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * Get confirmationToken
     *
     * @return string
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * Set invitationToken
     *
     * @param string $invitationToken
     *
     * @return UserInterface
     */
    public function setInvitationToken($invitationToken)
    {
        $this->invitationToken = $invitationToken;

        return $this;
    }

    /**
     * Get invitationToken
     *
     * @return string
     */
    public function getInvitationToken()
    {
        return $this->invitationToken;
    }

    /**
     * Set alteration token
     *
     * @param string $cancelAlterationToken
     *
     * @return User
     */
    public function setCancelAlterationToken($cancelAlterationToken)
    {
        $this->cancelAlterationToken = $cancelAlterationToken;

        return $this;
    }

    /**
     * Get alteration token
     *
     * @return string
     */
    public function getCancelAlterationToken()
    {
        return $this->cancelAlterationToken;
    }

    /**
     * Add role
     *
     * @param $role
     * @return $this
     */
    public function addRole($role)
    {
        if (is_array($role)) {
            foreach ($role as $_role) {
                $this->addRole($_role);
            }

            return $this;
        }

        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (false === in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * Remove role
     *
     * @param $role
     * @return $this
     */
    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * Set roles
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles)
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->roles;

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function isAdmin()
    {
        return $this->hasRole(static::ROLE_ADMIN) || $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    public function isSuperAdmin()
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    public function setSuperAdmin($boolean)
    {
        if (true === $boolean) {
            $this->addRole(static::ROLE_SUPER_ADMIN);
        } else {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }

        return $this;
    }

    /**
     * Set terms
     *
     * @param boolean $terms
     *
     * @return User
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;

        return $this;
    }

    /**
     * Get terms
     *
     * @return boolean
     */
    public function getTerms()
    {
        return $this->terms;
    }

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
     * Add sponsor
     *
     * @param SponsorInterface $sponsor
     * @return User
     */
    public function addSponsor(SponsorInterface $sponsor)
    {
        $this->sponsors[] = $sponsor;

        return $this;
    }

    /**
     * Remove sponsor
     *
     * @param SponsorInterface $sponsor
     */
    public function removeSponsor(SponsorInterface $sponsor)
    {
        $this->sponsors->removeElement($sponsor);
    }

    /**
     * Get sponsors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSponsors()
    {
        return $this->sponsors;
    }

}
