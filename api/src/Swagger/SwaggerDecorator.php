<?php

declare(strict_types=1);

namespace App\Swagger;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SwaggerDecorator implements NormalizerInterface
{
    private NormalizerInterface $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $docs = $this->decorated->normalize($object, $format, $context);

        $docs['components']['schemas']['Token'] = [
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'readOnly' => true,
                ],
            ],
        ];

        $docs['components']['schemas']['Register'] = [
            'type' => 'object',
            'properties' => []
        ];

        $docs['components']['schemas']['Credentials'] = [
            'type' => 'object',
            'properties' => [
                'email' => [
                    'type' => 'string',
                    'example' => 'email@email.com',
                ],
                'password' => [
                    'type' => 'string',
                    'example' => 'password',
                ],
            ],
        ];

        $docs['components']['schemas']['User Registration'] = [
            'type' => 'object',
            'properties' => [
                'email' => [
                    'type' => 'string',
                    'example' => 'email@email.com',
                ],
                'password' => [
                    'type' => 'string',
                    'example' => 'password',
                ],
            ],
        ];

        $docs['components']['schemas']['UserAlreadyExits'] = [
            'type' => 'object',
            'properties' => [
                'errors' => [
                    'type' => 'array',
                    'example' => ['User with email already exists'],
                ],
            ],
        ];

        $docs['components']['schemas']['InvalidRegisterData'] = [
            'type' => 'object',
            'properties' => [
                'errors' => [
                    'type' => 'array',
                    'example' => ['Invalid data'],
                ],
            ],
        ];

        $tokenDocumentation = [
            'paths' => [
                '/authentication_token' => [
                    'post' => [
                        'tags' => ['Token'],
                        'operationId' => 'postCredentialsItem',
                        'summary' => 'Get JWT token to login.',
                        'requestBody' => [
                            'description' => 'Create new JWT Token',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/Credentials',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            Response::HTTP_OK => [
                                'description' => 'Get JWT token',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/Token',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/register' => [
                    'post' => [
                        'tags' => ['User Registration'],
                        'operationId' => 'postCredentialsItem',
                        'summary' => 'Register a new user',
                        'requestBody' => [
                            'description' => 'Register a new user',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/User Registration',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            Response::HTTP_NO_CONTENT => [
                                'description' => 'Successful registration',
                            ],
                            Response::HTTP_BAD_REQUEST => [
                                'description' => 'Invalid register data',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/InvalidRegisterData',
                                        ],
                                    ],
                                ],
                            ],
                            Response::HTTP_CONFLICT => [
                                'description' => 'User with email already exist',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/UserAlreadyExits',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

        ];

        return array_merge_recursive($docs, $tokenDocumentation);
    }
}
