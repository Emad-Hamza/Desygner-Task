<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AdminRepository::class)
 */
class Admin extends BaseUser
{

    /**
     * Admin constructor.
     */
    public function __construct()
    {
        $this->setRoles(['ROLE_ADMIN']);
    }
}
