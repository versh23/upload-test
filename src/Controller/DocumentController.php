<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\DocumentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DocumentController extends AbstractController
{
    /**
     * @Route("/document", methods={"POST"})
     */
    public function index(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);

        $form = $this->createForm(DocumentType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $response = [];

                foreach ($form->getErrors(true, true) as $error) {
                    $response[(string) $error->getOrigin()->getPropertyPath()] = $error->getMessage();
                }

                return $this->json($response, 400);
            }

            dd($form->getData());
        }

        return $this->json([], 400);
    }
}
