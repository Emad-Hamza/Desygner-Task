<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User extends BaseUser
{
    /**
     * @ORM\ManyToMany(targetEntity=Image::class)
     */
    private $libraryImage;


    public function __construct()
    {
        $this->libraryImage = new ArrayCollection();
        $this->setRoles(['ROLE_USER']);
    }


    /**
     * @return Collection<int, Image>
     */
    public function getLibraryImage(): Collection
    {
        return $this->libraryImage;
    }

    public function addLibraryImage(Image $libraryImage): self
    {
        if (!$this->libraryImage->contains($libraryImage)) {
            $this->libraryImage[] = $libraryImage;
        }

        return $this;
    }

    public function removeLibraryImage(Image $libraryImage): self
    {
        $this->libraryImage->removeElement($libraryImage);

        return $this;
    }
}
