<?php

/*
 * This file is part of the hydrometer public server project.
 *
 * @author Clemens Krack <info@clemenskrack.com>
 */

namespace App\Security\Provider;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FacebookProvider implements UserProviderInterface
{
    protected $em;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    /**
     * Loads the user for the given facebookId.
     *
     * @return UserInterface
     */
    public function loadUserById($id)
    {
        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(['facebookId' => $id]);
        if ($user instanceof User) {
            return $user;
        }
    }

    /**
     * Loads the user for the given email, which is stored in username, too.
     *
     * @param string $username
     *
     * @throws UsernameNotFoundException if the user is not found
     *
     * @return UserInterface
     */
    public function loadUserByUsername($username)
    {
        $user = $this->loadUserByEmail($username);
        if ($user instanceof User) {
            return $user;
        }
        throw new UsernameNotFoundException();
    }

    /**
     * Loads the user for the given email.
     *
     * @param string $email
     *
     * @return UserInterface
     */
    public function loadUserByEmail($email)
    {
        // email can be empty, prevent searching for users with email IS NULL.
        if (empty($email)) {
            return;
        }

        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
        if ($user instanceof User) {
            return $user;
        }
    }

    /**
     * This is what we actually use in the Authenticator.
	 * First we try to look up the User by his Facebook-Id.
	 * Secondary, we try to find him by his email.
	 * This would mean, he is in the database, but has not used OAuth yet.
	 * If we do not find the user,
     */
    public function loadUserByOAuthUserResponse(FacebookUser $response)
    {
        $existing = $this->loadUserById($response->getId());

        if ($existing instanceof User) {
            return $existing;
        }

        // check if we already have this user
        $existing = $this->loadUserByEmail($response->getEmail());
        if ($existing instanceof User) {
            // update the facebook_id
            $existing->setFacebookId($response->getId());

            $this->em->persist($existing);
            $this->em->flush();

            return $existing;
        }

        // we don't know the user, create it
        $user = new User();

        $user->setEmail($response->getEmail());
        $user->setFacebookId($response->getId());
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @throws UnsupportedUserException  if the user is not supported
     * @throws UsernameNotFoundException if the user is not found
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->em->find(User::class, $user->getId());
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     */
    public function supportsClass($class): boolean
    {
        return User::class === $class;
    }
}
