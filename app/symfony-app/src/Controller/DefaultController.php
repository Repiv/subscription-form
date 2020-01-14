<?php

namespace App\Controller;

use App\Form\Type\SubscriptionType;
use App\Service\SubscriptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultController extends AbstractController
{
    /**
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param SubscriptionService $subscriptionService
     *
     * @return Response
     *
     * @Route("/", name="homepage")
     */
    public function index(
        Request $request,
        TranslatorInterface $translator,
        SubscriptionService $subscriptionService
    ): Response {
        $categories = array_flip($subscriptionService->getCategories());

        $form = $this->createForm(SubscriptionType::class, null, [
            'categories' => $categories,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$form->get('categories')->getData()) {
                $form->get('categories')->addError(new FormError($translator->trans('required')));
            } else {
                if (!$subscriptionService->saveFormData($form->getData())) {
                    $form->addError(new FormError($translator->trans('save_error')));
                } else {
                    return $this->redirectToRoute('success');
                }
            }
        }

        return $this->render('default/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param AuthenticationUtils $authenticationUtils
     *
     * @return Response
     *
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('default/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @return Response
     *
     * @Route("/success", name="success")
     */
    public function success(): Response
    {
        return $this->render('default/success.html.twig');
    }
}
