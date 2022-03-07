<?php

namespace App\Controller;

use App\Entity\User;
use App\Http\ApiResponse;
use App\Service\TranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    private $em;
    private $lexik_jwt_authentication_encoder;
    private $translatorService;
    private $encoder;

    public function __construct(
        EntityManagerInterface $em,
        JWTEncoderInterface $lexik_jwt_authentication_encoder,
        TranslationService $translatorService,
        UserPasswordHasherInterface $encoder
    ) {
        $this->em = $em;
        $this->lexik_jwt_authentication_encoder = $lexik_jwt_authentication_encoder;
        $this->translatorService = $translatorService;
        $this->encoder = $encoder;
    }

    /**
     * Enregistrement
     * 
     * @Route("/register", name="register", methods={"POST"})
     * 
     * @param Request $request
     * @return ApiResponse
     */
    public function register(Request $request): ApiResponse
    {
        $language = $this->translatorService->getLanguage($request, $this->getUser());

        $attr =  json_decode($request->getContent(), true);

        if (!$attr["email"]) {
            return new ApiResponse($this->translatorService->trans("message.register.email", [], $language), ["code" => 100], [], 401);
        }

        // vérifie que l'email n'existe pas déjà
        if($user = $this->em->getRepository(User::class)->findOneBy(["email" => $attr["email"]])) {
            return new ApiResponse($this->translatorService->trans("message.register.user", [], $language), ["code" => 101], [], 401);
        }

        if (!$attr["password"]) {
            return new ApiResponse($this->translatorService->trans("message.register.password", [], $language), ["code" => 104], [], 401);
        }
    
        $user = new User();
        $user->setEmail($attr["email"]);
        $user->setPassword($this->encoder->hashPassword($user, $attr["password"]));
        $user->setRoles(array("ROLE_USER"));
        $this->em->persist($user);


        $this->em->flush();

        $token = $this->lexik_jwt_authentication_encoder->encode([
            'username' => $user->getUsername(),
            'exp' => time() + 3600 // 1 hour expiration
        ]);

        $refreshToken = $this->lexik_jwt_authentication_encoder->encode([
            'username' => $user->getUsername(),
            'exp' => time() + (3600*24) // 1 jour expiration
        ]);

        $datas = [
            'token' => $token, 
            'refreshToken' => $refreshToken, 
            "role" => $user->getRoles(),
            'code' => 200
        ];

        return new ApiResponse("", $datas);
    }


    /**
     * Connexion
     * 
     * @Route("/login", name="login", methods={"POST"})
     * 
     *  
     * @param Request $request
     * @return ApiResponse
     */
    public function login(Request $request)
    {
        $language = $this->translatorService->getLanguage($request, $this->getUser());

        $attr =  json_decode($request->getContent(), true);

        if (!$attr["email"]) {
            return new ApiResponse($this->translatorService->trans("message.register.email", [], $language), ["code" => 100], [], 401);
        }

        if (!$attr["password"]) {
            return new ApiResponse($this->translatorService->trans("message.register.password", [], $language), ["code" => 104], [], 401);
        }

        if(!$user = $this->em->getRepository(User::class)->findOneBy(["email" => $attr["email"]])) {
            return new ApiResponse($this->translatorService->trans("message.register.user", [], $language), ["code" => 103], [], 401);
        }

        if(!$this->encoder->isPasswordValid($user, $attr["password"])) {
            return new ApiResponse($this->translatorService->trans("message.register.valid.password", [], $language), ["code" => 102], [], 401);
        }

        $token = $this->lexik_jwt_authentication_encoder->encode([
            'username' => $user->getUsername(),
            'exp' => time() + 3600 // 1 hour expiration
        ]);

        $refreshToken = $this->lexik_jwt_authentication_encoder->encode([
            'username' => $user->getUsername(),
            'exp' => time() + (3600*24) // 1 jour expiration
        ]);

        $datas = [
            'token' => $token, 
            'refreshToken' => $refreshToken, 
            "role" => $user->getRoles(),
            'code' => 200
        ];

        return new ApiResponse("", $datas);
    }
}