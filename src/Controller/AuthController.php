<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Exception;
use Firebase\JWT\JWT;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use OpenApi\Annotations as SWG;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/users")
 */
class AuthController extends AbstractFOSRestController
{
    /**
     * @FOSRest\Post("/register")
     * @SWG\Tag(name="users")
     * @SWG\Response(
     *     response=200,
     *     description="Get types",
     * )
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        $data = [
          'email' => $request->get('email'),
          'password' => $request->get('password')
        ];

        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($encoder->encodePassword($user, $request->get('password')));
            $user->setEmail($request->get('email'));
            $user->setRoles(["USER"]);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->handleView($this->view($user->getEmail(), Response::HTTP_CREATED));
        }

        return $this->handleView($this->view('Error', Response::HTTP_BAD_REQUEST));
    }

    /**
     * @FOSRest\Post("/login")
     * @SWG\Tag(name="users")
     * @SWG\Response(
     *     response=200,
     *     description="Get types",
     * )
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     * @throws Exception
     */
    public function login(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $encoder): Response
    {
        $user = $userRepository->findOneBy([
            "email" => $request->get('email'),
        ]);

        if (!$user || !$encoder->isPasswordValid($user, $request->get('password'))) {
            return $this->handleView($this->view('Email or password wrong', Response::HTTP_BAD_REQUEST));
        }
        $payload = [
            "user" => $user->getUsername(),
            "exp"  => (new \DateTime())->modify("+524160 minutes")->getTimestamp(),
        ];

        $jwt = JWT::encode($payload, $this->getParameter('jwt_secret'), 'HS256');

        return $this->handleView($this->view("Token : $jwt", Response::HTTP_OK));
    }
}
