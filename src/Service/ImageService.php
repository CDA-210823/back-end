<?php

namespace App\Service;

use App\Entity\Image;
use App\Repository\ImageRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageService
{

    public function uploadImage
    (UploadedFile $file, SluggerInterface $slugger, Image $imageEntity, ParameterBagInterface $container): bool
    {
        $availableExt = ['bin', 'png', 'jpg', 'svg', 'webp', 'jpeg'];
        if ($file) {
            $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFileName = $slugger->slug($originalFileName);
            $ext = $file->guessExtension();
            $newFileName = $safeFileName . '-' . uniqid() . $ext;
            $imageEntity->setName($newFileName);
            $imageEntity->setPath('/upload/' . $imageEntity->getName());
            $imageEntity->setExt($ext);

            if (!$ext) {
                $ext = 'bin';
            }

            if (in_array($ext, $availableExt) && $file->getSize() < 5000000) {
                $file->move($container->get('upload.directory'), $newFileName . '.' . $ext);
                return true;
            }
            return false;
        }
        return false;
    }
}