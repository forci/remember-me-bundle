<?php

/*
 * This file is part of the ForciRememberMeBundle package.
 *
 * (c) Martin Kirilov <wucdbm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Forci\Bundle\RememberMeBundle\Controller;

use Forci\Bundle\RememberMeBundle\Entity\RememberMeToken;
use Forci\Bundle\RememberMeBundle\Filter\RememberMeTokenFilter;
use Forci\Bundle\RememberMeBundle\Form\RememberMeToken\FilterType;
use Forci\Bundle\RememberMeBundle\Repository\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\RememberMe\AbstractRememberMeServices;

class RememberMeTokenController extends Controller {

    public function listAction(Request $request) {
        $repo = $this->container->get('remember_me.repo.remember_me_token');
        $filter = new RememberMeTokenFilter();
        $pagination = $filter->getPagination()->enable();
        $filterForm = $this->createForm(FilterType::class, $filter);
        $filter->load($request, $filterForm);
        $tokens = $repo->filter($filter);
        $data = [
            'entities' => $tokens,
            'filter' => $filter,
            'pagination' => $pagination,
            'filterForm' => $filterForm->createView()
        ];

        return $this->render('@RememberMe/RememberMeToken/list.html.twig', $data);
    }

    public function viewAction(int $id) {
        $token = $this->container->get('remember_me.repo.remember_me_token')->findOneById($id);

        if (!$token) {
            return $this->redirectToRoute('forci_remember_me_token_list');
        }

        $data = [
            'token' => $token
        ];

        return $this->render('@RememberMe/RememberMeToken/view.html.twig', $data);
    }

    public function deleteAction(int $id, Request $request) {
        $repo = $this->container->get('remember_me.repo.remember_me_token');
        $token = $repo->findOneById($id);

        $response = $this->getDeleteTokenResponse($request);

        if ($token) {
            $provider = $this->container->get('remember_me.provider.doctrine_entity_provider');
            $provider->deleteToken($token);

            $symfonySession = $request->getSession();
            /** @var SessionRepository $session */
            foreach ($token->getSessions() as $session) {
                if ($session->getIdentifier() == $symfonySession->getId()) {
                    $this->logout($token, $request, $response);
                    break;
                }
            }
        }

        return $response;
    }

    protected function getDeleteTokenResponse(Request $request): Response {
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'remove' => true
            ]);
        }

        if ($referer = $request->headers->get('referer')) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('forci_remember_me_token_list');
    }

    protected function logout(RememberMeToken $persistentToken, Request $request, Response $response) {
        $tokenStorage = $this->get('security.token_storage');
        $token = $tokenStorage->getToken();

        if (!$token) {
            return;
        }

        $tokenStorage->setToken(null);

        $serviceId = sprintf('security.authentication.rememberme.services.persistent.%s', $persistentToken->getArea());
        if ($this->container->has($serviceId)) {
            /** @var AbstractRememberMeServices $services */
            $services = $this->container->get($serviceId);
            $services->logout($request, $response, $token);
        }
    }

    public function refreshAction(int $id) {
        $repo = $this->container->get('remember_me.repo.remember_me_token');
        $token = $repo->findOneById($id);

        $data = [
            'entity' => $token
        ];

        return $this->render('@RememberMe/RememberMeToken/list_row.html.twig', $data);
    }
}
