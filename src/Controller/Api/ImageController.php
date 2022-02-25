<?php

namespace App\Controller\Api;

use App\Entity\Image;
use App\Form\ImageType;
use App\Services\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * Class ImageController
 * @package App\Controller\Api
 * @Route("/image")
 */
class ImageController extends AbstractController
{
    /**
     * @Route("/", name="api_image", methods={"GET"})
     * @SWG\Parameter(
     *     name="image[tags]",
     *     in="formData",
     *     description="Tags",
     *     type="array",
     *     @SWG\Items(type="string"),
     *     collectionFormat="multi",
     *     required=true
     *
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the  object of an Image",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Image::class, groups={"imageData"}),
     *     ),
     *
     * )
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/Api/ImageController.php',
        ]);
    }

    /**
     * @Route("/upload", name="api_image_upload", methods={"POST"})
     * @Rest\View(serializerGroups={"imageData"})
     * @SWG\Parameter(
     *     name="image[image]",
     *     in="formData",
     *     description="Profile image",
     *     type="file",
     *     required=true
     *
     * )
     * @SWG\Parameter(
     *     name="image[provider]",
     *     in="formData",
     *     description="Provider",
     *     type="string",
     *     required=true
     *
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the  object of an Image",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Image::class, groups={"imageData"}),
     *     ),
     *
     * )
     *
     *
     *
     * @SWG\Response(
     *     response=400,
     *     description="Bad request in form",
     *
     * )
     *
     * @param Request $request
     * @param FileUploader $fileUploader
     * @return Image|\Symfony\Component\Form\FormInterface
     */
    public function new(Request $request, FileUploader $fileUploader)
    {
//        dd($request->request->all());
//        die('asdasd');
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image, ['csrf_protection' => false]);
//        dd($form);
        $formRequest =  $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
//            print_r($form->get('image'));
//            die('asd');
//            $imageFile = $request->files->get('image');
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
//                die('asd');
                $fileUploader->upload($imageFile);
                $imageFileName = $fileUploader->getNewFileName();
                $image->setName($imageFileName);
//                var_dump($image);
                $em = $this->getDoctrine()->getManager();
                $em->persist($image);
                $em->flush();
                return $image;
            }
        }
        return $formRequest;

    }



}
