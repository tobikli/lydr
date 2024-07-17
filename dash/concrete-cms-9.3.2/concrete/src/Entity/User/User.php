<?php
namespace Concrete\Core\Entity\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\UserEntityInterface;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="Users",
 *     indexes={
 *     @ORM\Index(name="uEmail", columns={"uEmail"})
 *     }
 * )
 */
class User implements UserEntityInterface, \JsonSerializable
{
    /**
     * @ORM\Id @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $uID;

    /**
     * @ORM\OneToMany(targetEntity="\Concrete\Core\Entity\Notification\NotificationAlert", cascade={"remove"}, mappedBy="user")
     * @ORM\JoinColumn(name="uID", referencedColumnName="uID")
     *
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $alerts;

    /**
     * @ORM\OneToOne(targetEntity="\Concrete\Core\Entity\User\UserSignup", mappedBy="user", cascade={"remove"})
     */
    protected $signup;

    /**
     * @ORM\OneToMany(targetEntity="\Concrete\Core\Entity\Attribute\Value\UserValue", cascade={"remove"}, mappedBy="user")
     * @ORM\JoinColumn(name="uID", referencedColumnName="uID")
     */
    protected $attributes;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     */
    protected $uName;

    /**
     * @ORM\Column(type="string", length=254)
     */
    protected $uEmail;

    /**
     * 255 is the recommended width according to https://www.php.net/manual/en/password.constants.php
     * @ORM\Column(type="string", length=255)
     */
    protected $uPassword;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $uIsActive = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $uIsFullRecord = true;

    /**
     * @ORM\Column(type="boolean", options={"default"=-1})
     */
    protected $uIsValidated = -1;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $uDateAdded = null;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $uLastPasswordChange = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $uDateLastUpdated = null;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $uHasAvatar = false;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     */
    protected $uLastOnline = 0;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     */
    protected $uLastLogin = 0;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned": true, "default": 0})
     */
    protected $uPreviousLogin = 0;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     */
    protected $uNumLogins = 0;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     */
    protected $uLastAuthTypeID = 0;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $uLastIP;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $uTimezone;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $uDefaultLanguage;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $uIsPasswordReset = false;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"unsigned": true}, nullable=true)
     */
    protected $uHomeFileManagerFolderID = null;

    /**
     * @var string[]|null
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $ignoredIPMismatches;

    public function __construct()
    {
        $this->alerts = new ArrayCollection();
        $this->uLastPasswordChange = new \DateTime();
        $this->uDateLastUpdated = new \DateTime();
        $this->uDateAdded = new \DateTime();
        $this->ignoredIPMismatches = [];
    }

    /**
     * @return int
     */
    public function getUserID()
    {
        return $this->uID;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->uName;
    }

    /**
     * @return string
     */
    public function getUserEmail()
    {
        return $this->uEmail;
    }

    /**
     * @return string
     */
    public function getUserPassword()
    {
        return $this->uPassword;
    }

    /**
     * @return bool
     */
    public function isUserActive()
    {
        return $this->uIsActive;
    }

    /**
     * Returns false if there is no additional data but the email address.
     *
     * @return bool
     */
    public function isUserFullRecord()
    {
        return $this->uIsFullRecord;
    }

    /**
     * @return bool
     */
    public function isUserValidated()
    {
        return $this->uIsValidated;
    }

    /**
     * Gets the date a user was added to the system.
     *
     * @return \DateTime
     */
    public function getUserDateAdded()
    {
        return $this->uDateAdded;
    }

    /**
     * @return \DateTime
     */
    public function getUserLastPasswordChange()
    {
        return $this->uLastPasswordChange;
    }

    /**
     * @return \DateTime
     */
    public function getUserDateLastUpdated()
    {
        return $this->uDateLastUpdated;
    }

    /**
     * @return bool
     */
    public function userHasAvatar()
    {
        return $this->uHasAvatar;
    }

    /**
     * @return int
     */
    public function getUserLastOnline()
    {
        return $this->uLastOnline;
    }

    /**
     * @return int
     */
    public function getUserLastLogin()
    {
        return $this->uLastLogin;
    }

    /**
     * @return int|null
     */
    public function getUserPreviousLogin()
    {
        return $this->uPreviousLogin;
    }

    /**
     * @return int
     */
    public function getUserTotalLogins()
    {
        return $this->uNumLogins;
    }

    /**
     * @return int
     */
    public function getUserLastAuthenticationTypeID()
    {
        return $this->uLastAuthTypeID;
    }

    /**
     * @return string|null
     */
    public function getUserLastIP()
    {
        return $this->uLastIP;
    }

    /**
     * @return string|null
     */
    public function getUserTimezone()
    {
        return $this->uTimezone;
    }

    /**
     * @return string|null
     */
    public function getUserDefaultLanguage()
    {
        return $this->uDefaultLanguage;
    }

    /**
     * @return bool
     */
    public function isUserPasswordReset()
    {
        return $this->uIsPasswordReset;
    }

    /**
     * @param int $uID
     */
    public function setUserID($uID)
    {
        $this->uID = $uID;
    }

    /**
     * @param string $uName
     */
    public function setUserName($uName)
    {
        $this->uName = $uName;
    }

    /**
     * @param string $uEmail
     */
    public function setUserEmail($uEmail)
    {
        $this->uEmail = $uEmail;
    }

    /**
     * @param string $uPassword
     */
    public function setUserPassword($uPassword)
    {
        $this->uPassword = $uPassword;
    }

    /**
     * @param bool $uIsActive
     */
    public function setUserIsActive($uIsActive)
    {
        $this->uIsActive = $uIsActive;
    }

    /**
     * @param bool $uIsFullRecord
     */
    public function setUserIsFullRecord($uIsFullRecord)
    {
        $this->uIsFullRecord = $uIsFullRecord;
    }

    /**
     * @param bool $uIsValidated
     */
    public function setUserIsValidated($uIsValidated)
    {
        $this->uIsValidated = $uIsValidated;
    }

    /**
     * @param \DateTime $uDateAdded
     */
    public function setUserDateAdded($uDateAdded)
    {
        $this->uDateAdded = $uDateAdded;
    }

    /**
     * @param \DateTime $uLastPasswordChange
     */
    public function setUserLastPasswordChange($uLastPasswordChange)
    {
        $this->uLastPasswordChange = $uLastPasswordChange;
    }

    /**
     * @param \DateTime $uDateLastUpdated
     */
    public function setUserDateLastUpdated($uDateLastUpdated)
    {
        $this->uDateLastUpdated = $uDateLastUpdated;
    }

    /**
     * @param bool $uHasAvatar
     */
    public function setUserHasAvatar($uHasAvatar)
    {
        $this->uHasAvatar = $uHasAvatar;
    }

    /**
     * @param int $uLastOnline
     */
    public function setUserLastOnline($uLastOnline)
    {
        $this->uLastOnline = $uLastOnline;
    }

    /**
     * @param int $uLastLogin
     */
    public function setUserLastLogin($uLastLogin)
    {
        $this->uLastLogin = $uLastLogin;
    }

    /**
     * @param int|null $uPreviousLogin
     */
    public function setUserPreviousLogin($uPreviousLogin)
    {
        $this->uPreviousLogin = $uPreviousLogin;
    }

    /**
     * @param int $uNumLogins
     */
    public function setUserTotalLogins($uNumLogins)
    {
        $this->uNumLogins = $uNumLogins;
    }

    /**
     * @param int $uLastAuthTypeID
     */
    public function setUserLastAuthenticationTypeID($uLastAuthTypeID)
    {
        $this->uLastAuthTypeID = $uLastAuthTypeID;
    }

    /**
     * @param string $uLastIP
     */
    public function setUserLastIP($uLastIP)
    {
        $this->uLastIP = $uLastIP;
    }

    /**
     * @param string|null $uTimezone
     */
    public function setUserTimezone($uTimezone)
    {
        $this->uTimezone = $uTimezone;
    }

    /**
     * @param string|null $uDefaultLanguage
     */
    public function setUserDefaultLanguage($uDefaultLanguage)
    {
        $this->uDefaultLanguage = $uDefaultLanguage;
    }

    /**
     * @param bool $uIsPasswordReset
     */
    public function setUserIsPasswordReset($uIsPasswordReset)
    {
        $this->uIsPasswordReset = $uIsPasswordReset;
    }

    /**
     * @return int
     */
    public function getHomeFileManagerFolderID()
    {
        return $this->uHomeFileManagerFolderID;
    }

    /**
     * @param int $uHomeFileManagerFolderID
     */
    public function setHomeFileManagerFolderID($uHomeFileManagerFolderID)
    {
        $this->uHomeFileManagerFolderID = $uHomeFileManagerFolderID;
    }

    /**
     * @return string[]
     */
    public function getIgnoredIPMismatches(): array
    {
        return $this->ignoredIPMismatches;
    }

    /**
     * @param string[] $value
     *
     * @return $this
     */
    public function setIgnoredIPMismatches(array $value): self
    {
        $this->ignoredIPMismatches = $value;

        return $this;
    }

    /**
     * @return \Concrete\Core\User\UserInfo|null
     */
    public function getUserInfoObject()
    {
        return \UserInfo::getByID($this->getUserID());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getUserID();
    }

    /**
     * Return the user's identifier.
     *
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->getUserID();
    }

    /**
     * @return mixed|void
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'id' => $this->getUserID(),
            'name' => $this->getUserName(),
            'email' => $this->getUserEmail(),
        ];
    }

    /**
     * @return \Concrete\Core\Entity\Notification\NotificationAlert[]
     */
    public function getAlerts(): Collection
    {
        return $this->alerts;
    }
}
