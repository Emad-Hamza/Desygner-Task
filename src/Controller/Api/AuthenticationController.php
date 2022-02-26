<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\User;
use App\Entity\Admin;

class AuthenticationController extends AbstractController
{

    /**
     * User login.
     *
     * This call for login user.
     *
     * @Route("/login", methods={"POST"})
     * @SWG\Response(
     *     response=200,
     *     description="Returns the token and object of a user",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="token", type="string"),
     *          @SWG\Property(property="refresh_token", type="string"),
     *         @SWG\Property(property="user",ref=@Model(type=User::class, groups={"login"}))
     *     ),
     *
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Invalid credintials",
     *     @SWG\Schema(
     *         type="object",
     *
     *          @SWG\Property(property="code", type="string"),
     *          @SWG\Property(property="message", type="string")
     *     )
     *
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Missing/Extra form data",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="code", type="string"),
     *          @SWG\Property(property="message", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="credentials",
     *     in="body",
     *     description="email and password",
     *     @SWG\Schema(
     *         type="object",
     *          required={"email","password"},
     *          @SWG\Property(property="email", type="string",example="emad@desygner.com"),
     *          @SWG\Property(property="password", type="string",example="Test@password")
     *     )
     * )
     * @SWG\Tag(name="authentication")
     */
    public function user_login()
    {

    }

    /**
     * Admin login.
     *
     * This call for login user.
     *
     * @Route("/admin/login", methods={"POST"})
     * @SWG\Response(
     *     response=200,
     *     description="Returns the token and object of an admin",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="token", type="string"),
     *          @SWG\Property(property="refresh_token", type="string"),
     *         @SWG\Property(property="user",ref=@Model(type=User::class, groups={"login"}))
     *     ),
     *
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Invalid credintials",
     *     @SWG\Schema(
     *         type="object",
     *
     *          @SWG\Property(property="code", type="string"),
     *          @SWG\Property(property="message", type="string")
     *     )
     *
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Missing/Extra form data",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="code", type="string"),
     *          @SWG\Property(property="message", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="credentials",
     *     in="body",
     *     description="Admin's credentials",
     *     @SWG\Schema(
     *         type="object",
     *          required={"email","password"},
     *          @SWG\Property(property="email", type="string",example="admin@desygner.com"),
     *          @SWG\Property(property="password", type="string",example="Test@password")
     *     )
     * )
     * @SWG\Tag(name="authentication")
     */
    public function admin_login()
    {

    }
}
