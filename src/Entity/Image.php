<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 */
class Image
{
    const LOCAL = 'local';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMSSerializer\Groups({"uploaded","search"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMSSerializer\Groups({"uploaded","external", "search"})
     */
    private $provider;

    /**
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="images")
     * @JMSSerializer\Groups({"uploaded","external", "search"})
     */
    private $tags;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $externalUrl;

    /**
     * @JMSSerializer\Groups({"uploaded", "external", "search"})
     * @JMSSerializer\Accessor(getter="getUrl")
     * @JMSSerializer\Type("string")
     */
    private ?string $url = null;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    public function setExternalUrl(?string $externalUrl): self
    {
        $this->externalUrl = $externalUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }




}
