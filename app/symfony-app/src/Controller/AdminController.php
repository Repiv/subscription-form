<?php

namespace App\Controller;

use App\Form\Type\SubscriptionAdminType;
use App\Service\SubscriptionService;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @param Request $request
     * @param SubscriptionService $subscriptionService
     *
     * @return Response
     *
     * @Route("/", name="admin_homepage")
     */
    public function index(Request $request, SubscriptionService $subscriptionService): Response
    {
        $data = $subscriptionService->getData();
        $order = $request->get('order', 'name');
        $direction = $request->get('direction', 'ASC');
        $page = $request->get('page', 1);
        $perPage = 10;

        if (!in_array($order, ['date', 'name', 'email'])) {
            $order = 'name';
        }

        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }

        usort($data, function ($a, $b) use ($order, $direction) {
            return $direction === 'ASC' ? ($a[$order] > $b[$order]) : ($a[$order] < $b[$order]);
        });

        $pages = ceil(count($data) / $perPage);

        $data = array_slice($data, ($page - 1) * $perPage, $perPage);

        return $this->render('admin/index.html.twig', [
            'order' => $order,
            'direction' => $direction,
            'subscriptions' => $data,
            'categories' => $subscriptionService->getCategories(),
            'page' => $page,
            'pages' => $pages,
        ]);
    }

    /**
     * @param Request $request
     * @param string $id
     * @param SubscriptionService $subscriptionService
     * @param TranslatorInterface $translator
     *
     * @return Response
     *
     * @Route("/edit/{id}", name="admin_edit")
     */
    public function edit(
        Request $request,
        string $id,
        SubscriptionService $subscriptionService,
        TranslatorInterface $translator
    ): Response {
        $subscription = $subscriptionService->find($id);
        $form = $this->createForm(SubscriptionAdminType::class, $subscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$subscriptionService->editFormData($form->getData())) {
                $form->addError(new FormError($translator->trans('save_error')));
            } else {
                $this->addFlash('success', $translator->trans('admin.edited'));
                return $this->redirectToRoute('admin_homepage');
            }
        }

        return $this->render('admin/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param string $id
     * @param SubscriptionService $subscriptionService
     * @param TranslatorInterface $translator
     *
     * @return Response
     *
     * @Route("/delete/{id}", name="admin_delete")
     */
    public function delete(
        string $id,
        SubscriptionService $subscriptionService,
        TranslatorInterface $translator
    ): Response {
        $result = $subscriptionService->delete($id);

        if ($result) {
            $this->addFlash('primary', $translator->trans('admin.deleted'));
        } else {
            $this->addFlash('danger', $translator->trans('admin.not_deleted'));
        }

        return $this->redirectToRoute('admin_homepage');
    }
}