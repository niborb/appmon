<?php

namespace Rts\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;
use FR3D\LdapBundle\Model\LdapUserInterface;

/**
 * @ORM\Entity(repositoryClass="Rts\Bundle\UserBundle\Entity\UserRepository")
 * @ORM\Table(name="users")
 */
class User extends BaseUser implements LdapUserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Ldap Object Distinguished Name
     * @var string $dn
     */
    private $dn;

    /**
     * Ldap Object groups (memberof)
     * @var array $memberOf
     */
    protected $memberOf = array();

    /**
     * {@inheritDoc}
     */
    public function setDn($dn)
    {
        $this->dn = $dn;
    }

    /**
     * {@inheritDoc}
     */
    public function getDn()
    {
        return $this->dn;
    }

    /**
     * Set memberOf an array with all LDAP memberof attributes
     *
     * @param string|array $memberOf
     */
    public function setMemberOf($memberOf)
    {
        if (!is_array($memberOf)) {
            $memberOf = array($memberOf);
        }
        $this->memberOf = $memberOf;
    }


    /**
     * Get memberOf, set by setMemberOf.
     * An array with all LDAP memberof attributes
     *
     * @return array
     */
    public function getMemberOf()
    {
        return $this->memberOf;
    }

}
