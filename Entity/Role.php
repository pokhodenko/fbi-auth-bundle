<?php

namespace FBI\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Role
 *
 * @ORM\Table(name="role")
 * @ORM\Entity(repositoryClass="FBI\UserBundle\Repository\RoleRepository")
 */
class Role implements RoleInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=255, unique=true)
     */
    private $role;

    /**
     * @var User[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="FBI\UserBundle\Entity\User", inversedBy="roles")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $users;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;


    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * Named constructor
     *
     * @param string $role
     * @return Role
     */
    public static function create($role) {
        $instance = new self();
        $instance->setRole($role);
        return $instance;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $role
     *
     * @return Role
     */
    public function setRole($role)
    {
        $role = trim(strtoupper($role), '_');
        if (strpos($role, 'ROLE_') === FALSE) {
            $role = 'ROLE_'.$role;
        }
        $this->role = $role;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Role
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * User setter
     *
     * @param User $user
     * @return $this
     */
    public function setUser(User $user) {
        $this->users->add($user);

        return $this;
    }

    /**
     * User getter
     *
     * @return User
     */
    public function getUsers() {
        return $this->users;
    }
}

