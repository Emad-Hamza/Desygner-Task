<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 8/9/2021
 * Time: 4:51 PM
 */

namespace App\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

class FileUploader
{
    private $targetDirectory;
    private $newFileName;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
        $this->newFileName = $fileName;


        try {
            $file->move($this->getTargetDirectory() . '/', $fileName);

        } catch (FileException $e) {
            return new JsonResponse('fail', 400);
        }

        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    /**
     * @return mixed
     */
    public function getNewFileName()
    {
        return $this->newFileName;
    }


}
