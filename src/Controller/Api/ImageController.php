<?php

namespace App\Controller\Api;

use App\Entity\Image;
use App\Entity\Tag;
use App\Form\ExternalImageType;
use App\Form\ImageType;
use App\Form\UploadedImageType;
use App\Repository\TagRepository;
use App\Services\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class ImageController
 * @package App\Controller\Api
 * @Route("/image")
 */
class ImageController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @Route("/", name="api_image", methods={"POST"})
     * @SWG\Parameter(
     *     name="image[tags]",
     *     in="formData",
     *     description="Tags",
     *     type="array",
     *     @SWG\Items(type="string"),
     *     collectionFormat="multi",
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
    public function index(Request $request): Response
    {
        print_r($request->getContent());
        die('zxc');

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/Api/ImageController.php',
        ]);
    }

    /**
     * @Route("/upload/new", name="api_image_upload/new", methods={"POST"})
     * @Rest\View(serializerGroups={"uploaded"})
     *
     *
     * @SWG\Parameter(
     *     name="uploaded_image[image]",
     *     in="formData",
     *     description="Profile image",
     *     type="file",
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="tags[]",
     *     in="query",
     *     description="Tags",
     *     type="array",
     *     collectionFormat="multi",
     *     @SWG\Items(type="string"),
     *     required=false,
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the  object of an Image",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Image::class, groups={"uploaded"}),
     *     ),
     *
     * )
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
    public function new(Request $request, FileUploader $fileUploader, TagRepository $tagRepository)
    {
        $image = new Image();
        $form = $this->createForm(UploadedImageType::class, $image, ['csrf_protection' => false]);
        $formRequest = $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $fileUploader->upload($imageFile);
                $imageFileName = $fileUploader->getNewFileName();
                $image->setName($imageFileName);
                $image->setProvider('local');
                $em = $this->getDoctrine()->getManager();

                $submittedTags = $request->query->get('tags');
                foreach ($submittedTags as $submittedTag) {
                    $tag = $tagRepository->findOneByName($submittedTag);
                    if ($tag) {
                        $image->addTag($tag);
                    } else {
                        $tag = new Tag();
                        $tag->setName($submittedTag);
                        $em->persist($tag);
                        $em->flush();
                        $image->addTag($tag);
                    }
                }


                $em->persist($image);
                $em->flush();
                return $image;
            }
        }
        return $formRequest;

    }

    /**
     * @Route("/external/new", name="api_image_external_new", methods={"POST"})
     * @Rest\View(serializerGroups={"external"})
     *
     * @SWG\Parameter(
     *     name="external_image[provider]",
     *     in="formData",
     *     description="Provider",
     *     type="string",
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="external_image[externalUrl]",
     *     in="formData",
     *     description="External image url",
     *     type="string",
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="tags[]",
     *     in="query",
     *     description="Tags",
     *     type="array",
     *     collectionFormat="multi",
     *     @SWG\Items(type="string"),
     *     required=false,
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the  object of an Image",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=Image::class, groups={"external"}),
     *     ),
     *
     * )
     *
     *
     * @SWG\Response(
     *     response=400,
     *     description="Bad request in form",
     *
     * )
     *
     * @param Request $request
     * @return Image|\Symfony\Component\Form\FormInterface
     */
    public function newExternal(Request $request, TagRepository $tagRepository)
    {
        $em = $this->getDoctrine()->getManager();
//        print_r($request->request->all());
//        die('zxc');
        $image = new Image();
        $form = $this->createForm(ExternalImageType::class, $image, ['csrf_protection' => false]);
        $formRequest = $form->handleRequest($request);
//        print_r($form->getData());
//        die('xzc');
        if ($form->isSubmitted() && $form->isValid()) {
//            print_r($form->get('tags')->getData());
//            die('xzc');

            $response = $this->httpClient->request(
                'GET',
                $form->get('externalUrl')->getData()
            );

            if ($response) {
                if (str_starts_with($response->getHeaders()['content-type'][0], 'image/')) {
                    $submittedTags = $request->query->get('tags');
                    foreach ($submittedTags as $submittedTag) {
                        $tag = $tagRepository->findOneByName($submittedTag);
                        if ($tag) {
                            $image->addTag($tag);
                        } else {
                            $tag = new Tag();
                            $tag->setName($submittedTag);
                            $em->persist($tag);
                            $em->flush();
                            $image->addTag($tag);
                        }
                    }

                    $em->persist($image);
                    $em->flush();
                    return $image;
                }
            }
        }
        return $formRequest;

    }


}
