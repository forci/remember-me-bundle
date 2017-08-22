<?php

namespace Forci\Bundle\RememberMeBundle\Controller;

use Forci\Bundle\RememberMeBundle\Filter\RememberMeTokenFilter;
use Forci\Bundle\RememberMeBundle\Form\RememberMeToken\FilterType;
use Frontend\Controller\BaseController;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class RememberMeTokenController extends BaseController {

    public function listAction(Request $request) {
        $repo = $this->container->get('remember_me.repo.remember_me_token');
        $filter = new RememberMeTokenFilter();
        $pagination = $filter->getPagination()->enable();
        $filterForm = $this->createForm(FilterType::class, $filter);
        $filter->load($request, $filterForm);
        $tokens = $repo->filter($filter);
        $data = [
            'entities'   => $tokens,
            'filter'     => $filter,
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

        if ($token) {
            $provider = $this->container->get('remember_me.provider.doctrine_entity_provider');
            $provider->deleteToken($token);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'remove'  => true
            ]);
        }

        if ($referer = $request->headers->get('referer')) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('forci_remember_me_token_list');
    }

    public function deleteSessionAction(int $id, Request $request) {
        $repo = $this->container->get('remember_me.repo.session');
        $session = $repo->findOneById($id);

        if ($session) {
            try {
                /** @var \SessionHandlerInterface $sessionHandler */
                $sessionHandler = $this->container->get('remember_me.session_handler');
                $sessionHandler->destroy($session->getIdentifier());
            } catch (ServiceNotFoundException $e) {
                // service not found
            }

            $repo->remove($session);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'remove'  => true
            ]);
        }

        if ($referer = $request->headers->get('referer')) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('forci_remember_me_token_list');
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