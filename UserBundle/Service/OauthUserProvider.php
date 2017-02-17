<?php
/**
 * @file
 * File description. 
 */

namespace FBI\UserBundle\Service;


use Doctrine\Common\Persistence\ManagerRegistry;
use FBI\UserBundle\Entity\Role;
use FBI\UserBundle\Entity\User;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OauthUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface {

    private $doctrine;
    private $passwordEncoder;


    public function __construct(ManagerRegistry $doctrine, UserPasswordEncoder $encoder)
    {
        $this->doctrine = $doctrine;
        $this->passwordEncoder = $encoder;
    }
    /**
     * Loads the user by a given UserResponseInterface object.
     *
     * @param UserResponseInterface $response
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        try {
            $user = $this->loadUserByUsername($response->getNickname());
        }
        catch (UsernameNotFoundException $e) {
            $user = new User();

            $user
                ->setFirstName($response->getFirstName())
                ->setLastName($response->getLastName())
                ->setUsername($response->getNickname())
                ->setEmail($response->getEmail())
            ;

            $workerRole = $this->doctrine->getRepository('FBIUserBundle:Role')->findOneBy(['role' => 'ROLE_WORKER']);

            if (!$workerRole) {
                $workerRole = Role::create('WORKER');
            }

            $user->addRole($workerRole);

            // Just set some unique string as a password.
            $user->setPassword($this->passwordEncoder->encodePassword($user, uniqid()));
        }

        $user->setLastLoginAt(new \DateTime());

        $em = $this->doctrine->getManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return User
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        $user = $this->doctrine->getRepository('FBIUserBundle:User')->findOneBy(['username' => $username]);

        if (null === $user) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username)
            );
        }

        return $user;
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->doctrine->getRepository(User::class)->find($user->getId());
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return ($class instanceof User);
    }


}