<?php

namespace App\Controller\Api;

use App\Entity\Image;
use App\Entity\Tag;
use App\Form\ExternalImageType;
use App\Form\ImageType;
use App\Form\UploadedImageType;
use App\Repository\ImageRepository;
use App\Repository\TagRepository;
use App\Services\FileUploader;
use FOS\RestBundle\Serializer\Serializer;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * Add new image through direct upload
     * @Route("/upload/new", name="api_image_upload_new", methods={"POST"})
     * @Rest\View(serializerGroups={"uploaded"})
     * @Security("has_role('ROLE_ADMIN')")
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
     * @SWG\Response(
     *     response="401",
     *     description="When the user is not authenticated",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="code", type="string"),
     *          @SWG\Property(property="message", type="string")
     *     )
     * )
     *
     * @SWG\Response(
     *     response="403",
     *     description="When the user not allowed to do this action.",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="code", type="string"),
     *          @SWG\Property(property="message", type="string")
     *     )
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Bad request in form",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="code", type="string"),
     *          @SWG\Property(property="message", type="string")
     *     )
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
        $form->handleRequest($request);

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
        throw new BadRequestHttpException('Form is not valid.');
    }

    /**
     * Add new image through external url
     * @Route("/external/new", name="api_image_external_new", methods={"POST"})
     * @Rest\View(serializerGroups={"external"})
     * @Security("has_role('ROLE_ADMIN')")
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
     * @SWG\Response(
     *     response="401",
     *     description="When the user is not authenticated",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="code", type="string"),
     *          @SWG\Property(property="message", type="string")
     *     )
     * )
     *
     * @SWG\Response(
     *     response="403",
     *     description="When the user not allowed to do this action.",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="code", type="string"),
     *          @SWG\Property(property="message", type="string")
     *     )
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Bad request in form",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="code", type="string"),
     *          @SWG\Property(property="message", type="string")
     *     )
     * )
     *
     * @param Request $request
     * @param TagRepository $tagRepository
     * @return Image|\Symfony\Component\Form\FormInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function newExternal(Request $request, TagRepository $tagRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $image = new Image();
        $form = $this->createForm(ExternalImageType::class, $image, ['csrf_protection' => false]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $response = $this->httpClient->request(
                'GET',
                $form->get('externalUrl')->getData()
            );

            if ($response) {
                if (str_starts_with($response->getHeaders()['content-type'][0], 'image/')) {
                    $submittedTags = $request->query->get('tags');
                    if ($submittedTags) {
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
                    }

                    $em->persist($image);
                    $em->flush();
                    return $image;
                }
            }
        }
        throw new BadRequestHttpException('Form is not valid.');
    }

    /**
     * Search images by tag and provider
     * @Route("/search/{tag}", name="api_image_search", methods={"GET"})
     * @Rest\View(serializerGroups={"external"})
     * @Security("has_role('ROLE_USER')")
     *
     * @SWG\Parameter(
     *     name="tag",
     *     in="path",
     *     type="string",
     *     required=true,
     * )
     *
     * @SWG\Parameter(
     *     name="provider",
     *     in="query",
     *     type="string",
     *     required=false,
     * )
     *
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="integer",
     *     required=false,
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the  collection of Images objects that match the search criteria.",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *            property="images",
     *            @SWG\Items(type="object",
     *                ref=@Model(type=Image::class, groups={"search"})),
     *         ),
     *         @SWG\Property(property="current_page", type="integer"),
     *         @SWG\Property(property="next_page", type="integer"),
     *         @SWG\Property(property="previous_page", type="integer")
     *
     * )
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Page value is not found.",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="code", type="string"),
     *          @SWG\Property(property="message", type="string")
     *     )
     * )
     *
     * @param string $tag
     * @param ImageRepository $imageRepository
     */
    public function search(string $tag, Request $request,
                           ImageRepository $imageRepository,
                           PaginatorInterface $paginator)
    {
        $provider = $request->query->get('provider');
        $query = $imageRepository->findAllByTagAndProviderQuery($tag, $provider);
//        $query   = $imageRepository->createQueryBuilder('i');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        $totalNumberOfPages = floor($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage());

        $nextPage = null;
        $previousPage = null;
        
        if ( $totalNumberOfPages != 0 &&
        ($pagination->getCurrentPageNumber() > $totalNumberOfPages
        || $pagination->getCurrentPageNumber() < 1)) {
            throw new BadRequestHttpException('Page value is not valid');
        }
        if ($pagination->getCurrentPageNumber() < $totalNumberOfPages) {
            $nextPage = $pagination->getCurrentPageNumber() +1;
        }

        if ($pagination->getCurrentPageNumber() > 1) {
            $previousPage = $pagination->getCurrentPageNumber() -1;
        }

            
        return ['images' => $pagination->getItems(),
            'current_page'=>$pagination->getCurrentPageNumber(),
            'next_page' => $nextPage,
            'prevoious_page' => $previousPage
            ];


    }


}
