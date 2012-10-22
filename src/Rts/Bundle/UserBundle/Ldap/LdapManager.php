<?php

namespace Rts\Bundle\UserBundle\Ldap;

use FR3D\LdapBundle\Ldap\LdapManager as BaseLdapManager;
use FR3D\LdapBundle\Model\LdapUserInterface;
use Rts\Bundle\UserBundle\Entity\User;
use FR3D\LdapBundle\Driver\LdapConnectionInterface;
use Doctrine\ORM\EntityManager;

/**
 * Ldap Manager class which will be responsible for
 * Updating the internal roles based on the configured
 * LDAP role mapping.
 */
class LdapManager extends BaseLdapManager
{

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * C'tor
     *
     * @param \FR3D\LdapBundle\Driver\LdapConnectionInterface $connection
     * @param $userManager
     * @param \Doctrine\ORM\EntityManager $em
     * @param array $params
     * @param array $mapping
     */
    public function __construct(
        LdapConnectionInterface $connection,
        $userManager,
        EntityManager $em,
        array $params = array(),
        array $mapping = array()
    ) {
        $this->mapping = $mapping;
        $this->em = $em;
        parent::__construct($connection, $userManager, $params);
    }

    /**
     * Use the parents hydrate first and after this do the LDAP
     * roles to internal roles mapping.
     *
     * @param \FR3D\LdapBundle\Model\LdapUserInterface $user
     * @param array $entry
     * @return \FR3D\LdapBundle\Model\LdapUserInterface
     */
    protected function hydrate(LdapUserInterface $user, array $entry)
    {
        $ldapManager = parent::hydrate($user, $entry);

        if (!empty($this->mapping)
            && !empty($entry['memberof'])
            && $user instanceof User) {

            /** @var $objUser \Rts\Bundle\UserBundle\Entity\User */
            $objUser = $this->em->getRepository(get_class($user))
                ->findOneBy(array('username' => $user->getUsername()));

            if ($objUser) {
                $roles = array();
                $this->em->persist($objUser);
                $objUser->setRoles(array());
                foreach ($this->mapping as $role => $ldapRoles) {
                    foreach ($ldapRoles as $ldapRole) {
                        if (in_array($ldapRole, $entry['memberof'])) {
                            $roles[] = $role;
                        }
                    }
                }
                $objUser->setRoles($roles);
                $this->em->flush();
            }

            $user->setRoles(array());
            foreach ($this->mapping as $role => $ldapRoles) {
                foreach ($ldapRoles as $ldapRole) {
                    if (in_array($ldapRole, $entry['memberof'])) {
                        $user->addRole($role);
                    }
                }
            }
        }

        return $ldapManager;
    }

}
