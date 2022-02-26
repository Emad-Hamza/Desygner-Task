<?php

namespace App\Controller\Api;

use App\Entity\Image;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Swagger\Annotations as SWG;

/**
 * Class UserController
 * @package App\Controller\Api
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * Add image to personal library
     * @Route("/library/add/{image}", name="app_api_user_library_add", methods={"PUT"})
     * @Security("has_role('ROLE_USER')")
     * Adding image to user's library
     * @SWG\Response(
     *     response=200,
     *     description="Added successfully",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="code", type="string"),
     *          @SWG\Property(property="message", type="string")
     * )
     * )
     *
     * @SWG\Response(
     *     response="404",
     *     description="when the image is not found",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="code", type="string"),
     *          @SWG\Property(property="message", type="string")
     *     )
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
     * @param Image $image
     * @return Response
     */
    public function addImageToLibrary(Image $image): Response
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            $em = $this->getDoctrine()->getManager();
            $user->addLibraryImage($image);
            $em->persist($user);
            $em->flush();

            return new JsonResponse(['code'=> 200, 'message'=>'Successfully added image to library'], 200);
        }
        else
        {
            throw new AccessDeniedException();
        }
    }
}
