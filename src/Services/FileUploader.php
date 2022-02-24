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
        $commonUniqId = uniqid(); //to be used in uploading the file and in the qpdf output
        $fileName = $safeFilename . '-' . $commonUniqId . '.' . $file->guessExtension();
        $this->newFileName = $fileName;
        //        $fileDirectory = $this->getTargetDirectory() . '/' . $fileName;
//        $pathPlusFileName = $fileDirectory . '/' . $fileName;


        /*did this match the default qpdf result default pattern (%d before the file extension)
        which was working fine by default on windows but not on ubuntu
        */
        $eachPagePathPlusName = $this->getTargetDirectory() . '/' . $fileName . '/' .
            $safeFilename . '-' . $commonUniqId . "-%d." . $file->guessExtension();

        try {
            $file->move($this->getTargetDirectory() . '/' . $fileName . '/', $fileName);
//            var_dump(exec(("qpdf --decrypt $pathPlusFileName --replace-input 2>&1")));
//            var_dump(exec("qpdf --split-pages $pathPlusFileName $eachPagePathPlusName  2>&1"));
//            unlink($pathPlusFileName);

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
