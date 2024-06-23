<?php

namespace App\Services;

use App\Models\User;
use Carbon\WrapperClock;
use Illuminate\Support\Str;
use DateTimeImmutable;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Exception;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Parser as ParserInterface;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\Validator;

class JwtService
{
    private Key $key;

    private ParserInterface $parser;

    public function __construct(
        private JwtFacade $jwt,
    ) {
        $this->key = InMemory::plainText(config('jwt.key'));
        $this->parser = new Parser(new JoseEncoder());
    }

    public function issue(User $user): Token
    {
        return $this->jwt->issue(
            signer: $this->signingAlgorithm(),
            signingKey: $this->key,
            customiseBuilder: function (
                Builder $builder,
                DateTimeImmutable $issuedAt
            ) use ($user) : Builder {
                return $builder
                    ->issuedBy(config('app.url'))
                    ->permittedFor(config('app.front_url'))
                    ->identifiedBy($this->newUniqueId())
                    ->withClaim('uid', $user->uuid)
                    ->withClaim('iid', $user->id)
                    ->expiresAt($issuedAt->modify('+1 hour'));
            }
        );
    }

    public function validateToken($token): ?Token
    {

        try {
            $token = $this->parser->parse($token);

            $validator = new Validator();
            $validator->assert($token,
                new SignedWith($this->signingAlgorithm(), $this->key),
                new StrictValidAt(new WrapperClock(now())),
                new IssuedBy(config('app.url')),
            );
            return $token;
        } catch (Exception $e) {}

        return null;
    }

    private function signingAlgorithm(): Signer
    {
        return new Sha256();
    }

    public function newUniqueId()
    {
        return (string) Str::orderedUuid();
    }
}
