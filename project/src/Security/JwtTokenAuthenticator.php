<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class JwtTokenAuthenticator extends AbstractGuardAuthenticator
{
    private $jwtEncoder;
    private $em;
    private $encoder;

    public function __construct(JWTEncoderInterface $jwtEncoder, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        $this->jwtEncoder = $jwtEncoder;
        $this->em = $em;
        $this->encoder = $encoder;
    }

    public function supports(Request $request)
    {
        if($request->headers->has('Authorization') 
        && 0 === strpos($request->headers->get('Authorization'), 'Bearer ')) {
            return true;
        }

        throw new Exception("Header request not content bearer authorization", 401);  
    }

    public function getCredentials(Request $request)
    {
        $extractor = new AuthorizationHeaderTokenExtractor('Bearer', 'Authorization');

        $token = $extractor->extract($request);

        if (!$token) {
            return;
        }

        return $token;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {

        $data = $this->jwtEncoder->decode($credentials);


        if ($data === false) {
            throw new CustomUserMessageAuthenticationException('Invalid Token');
        }

        // si le temps du token est passé
        // if(strtotime("now") > $data["exp"]) {
        //     throw new CustomUserMessageAuthenticationException('Invalid Token');
        // }

        return $this->em->getRepository(User::class)->findOneBy(['email' => $data['username']]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // do nothing - let the controller be called
    }

    public function supportsRememberMe()
    {
        return false;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new Response('', 401, ['WWW-Authenticate' => 'Bearer']);
    }

    
}

