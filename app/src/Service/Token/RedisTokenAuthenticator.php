<?php

namespace App\Service\Token;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class RedisTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private RedisTokenManager $tokenManager,
        private UserRepository $userRepository,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->headers->get('Authorization', ''), 'Bearer ');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $token = str_replace('Bearer ', '', $request->headers->get('Authorization', ''));

        $userId = $this->tokenManager->getUserIdByToken($token);

        if (!$userId) {
            throw new AuthenticationException('Invalid or expired token');
        }

        return new SelfValidatingPassport(new UserBadge((string) $userId, fn () => $this->userRepository->find($userId)
        ));
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?JsonResponse
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        return new JsonResponse(['error' => 'Unauthorized'], 401);
    }
}
