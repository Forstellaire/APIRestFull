<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Exception;
use Firebase\JWT\JWT;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use OpenApi\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/api/test")
 */
class TestController extends AbstractFOSRestController
{
    /**
     * @FOSRest\Get("")
     * @SWG\Tag(name="test")
     * @SWG\Response(
     *     response=200,
     *     description="Get types",
     * )
     * @return Response
     */
    public function index(UserRepository $userRepository): Response
    {
        $user = $this->getUser();

        return $this->handleView($this->view($user, Response::HTTP_OK));
    }
}
