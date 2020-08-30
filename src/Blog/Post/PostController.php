<?php

namespace App\Blog\Post;

use App\Blog\Entity\Post;
use App\Entity\User;
use App\ViewRenderer;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Transaction;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Http\Method;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\Web\User\User as UserComponent;

final class PostController
{
    private ViewRenderer $viewRenderer;

    public function __construct(ViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withControllerName('blog/post');
    }

    public function index(Request $request, PostRepository $postRepository, ResponseFactoryInterface $responseFactory): Response
    {
        $slug = $request->getAttribute('slug', null);
        $item = $postRepository->fullPostPage($slug);
        if ($item === null) {
            return $responseFactory->createResponse(404);
        }

        return $this->viewRenderer->render('index', ['item' => $item]);
    }

    public function add(
        Request $request,
        ORMInterface $orm,
        ResponseFactoryInterface $responseFactory,
        UrlGeneratorInterface $urlGenerator,
        UserComponent $userComponent
    ): Response
    {
        $body = $request->getParsedBody();
        $parameters = [
            'body' => $body,
        ];

        if ($request->getMethod() === Method::POST) {
            $sent = false;
            $error = '';

            try {
                foreach (['header', 'content'] as $name) {
                    if (empty($body[$name])) {
                        throw new \InvalidArgumentException(ucfirst($name) . ' is required');
                    }
                }

                $post = new Post($body['header'], $body['content']);

                $userRepo = $orm->getRepository(User::class);
                $user = $userRepo->findByPK($userComponent->getId());

                $post->setUser($user);

                $transaction = new Transaction($orm);
                $transaction->persist($post);

                $transaction->run();

                $sent = true;

                return $responseFactory
                    ->createResponse(302)
                    ->withHeader(
                        'Location',
                        $urlGenerator->generate('blog/index')
                    );
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }

            $parameters['sent'] = $sent;
            $parameters['error'] = $error;
        }

        $parameters['title'] = 'Add post';
        return $this->viewRenderer->withCsrf()->render('__form', $parameters);
    }

    public function edit(
        Request $request,
        ORMInterface $orm,
        ResponseFactoryInterface $responseFactory,
        UrlGeneratorInterface $urlGenerator,
        PostRepository $postRepository): Response
    {
        $body = $request->getParsedBody();
        $parameters = [
            'body' => $body,
        ];

        if ($request->getMethod() === Method::POST) {
            try {
                foreach (['header', 'content', 'slug'] as $name) {
                    if (empty($body[$name])) {
                        throw new \InvalidArgumentException(ucfirst($name) . ' is required');
                    }
                }

                $post = $postRepository->fullPostPage($body['slug']);
                if ($post === null) {
                    return $responseFactory->createResponse(404);
                }

                $post->setTitle($body['header']);
                $post->setContent($body['content']);

                $transaction = new Transaction($orm);
                $transaction->persist($post);

                $transaction->run();

                return $responseFactory
                    ->createResponse(302)
                    ->withHeader(
                        'Location',
                        $urlGenerator->generate('blog/index')
                    );
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }

            $parameters['sent'] = false;
            $parameters['error'] = $error;
        }

        $parameters['title'] = 'Edit post';
        return $this->viewRenderer->withCsrf()->render('__form', $parameters);
    }
}
