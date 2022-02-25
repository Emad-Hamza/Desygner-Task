<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 8/30/2021
 * Time: 4:37 PM
 */

namespace App\EventListener;


use App\Entity\Image;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ImagePostLoad
{
    public function postLoad(Image $image, LifecycleEventArgs $event): void
    {
        global $kernel;
        if($image->getProvider() == Image::LOCAL)
        {
            $imagePublicUrl = $kernel->getContainer()->getParameter('base_url').
            'uploads'. $image->getName();
        $image->setUrl($imagePublicUrl);
        }
        else {
            $image->setUrl($image->getExternalUrl());
        }
    }

    public function postPersist(Image $image, LifecycleEventArgs $event): void
    {
        global $kernel;
        if($image->getProvider() == Image::LOCAL)
        {
            $imagePublicUrl = $kernel->getContainer()->getParameter('base_url').
                '/uploads/'. $image->getName();
            $image->setUrl($imagePublicUrl);
        }
        else {
            $image->setUrl($image->getExternalUrl());
        }
    }



}