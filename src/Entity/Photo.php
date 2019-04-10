<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


/**
 * @ORM\Entity(repositoryClass="App\Repository\PhotoRepository")
 * @Vich\Uploadable
 */
class Photo
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photoName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="photo", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="photos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="photo_file", fileNameProperty="photoName")
     *
     * @var File
     */
    private $photo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PhotoLike", mappedBy="photo")
     */
    private $likes;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->likes    = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhotoName(): ?string
    {
        return $this->photoName;
    }

    public function setPhotoName(?string $photoName): self
    {
        $this->photoName = $photoName;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setPhoto($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getPhoto() === $this) {
                $comment->setPhoto(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param File $photo
     */
    public function setPhoto(File $photo): void
    {
        $this->photo = $photo;
    }

    /**
     * @return Collection|PhotoLike[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(PhotoLike $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->setPhoto($this);
        }

        return $this;
    }

    public function removeLike(PhotoLike $like): self
    {
        if ($this->likes->contains($like)) {
            $this->likes->removeElement($like);
            // set the owning side to null (unless already changed)
            if ($like->getPhoto() === $this) {
                $like->setPhoto(null);
            }
        }

        return $this;
    }

    public function isLikedByUser(User $user) :bool
    {
        /** @var PhotoLike $like */
        foreach ($this->likes as $like) {
            if ($like->getUser() === $user)  return true;
        }

        return false;
    }
}
