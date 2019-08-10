<?php

declare(strict_types=1);

namespace App\Controller;

use League\Flysystem\FilesystemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UploadController extends AbstractController
{
    private $storage;

    public function __construct(FilesystemInterface $defaultStorage)
    {
        $this->storage = $defaultStorage;
    }

    /**
     * @Route("/upload", methods={"POST"})
     */
    public function upload(Request $request, ValidatorInterface $validator)
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json([], 400);
        }

        $errors = $validator->validate($file, new File([
            //Это например можно вынести в конфиг
            'maxSize' => '2M',
            'mimeTypes' => ['application/pdf', 'application/x-pdf', 'image/*'],
        ]));

        if ($errors->count()) {
            $array = [];
            foreach ($errors as $error) {
                $array[] = $error->getMessage();
            }

            return $this->json($array, 400);
        }

        $path = $file->getClientOriginalName();

        $stream = fopen($file->getPathname(), 'r+b');
        $this->storage->putStream($path, $stream, [
            'mimetype' => $file->getMimeType(),
        ]);

        if (is_resource($stream)) {
            fclose($stream);
        }

        $fileEntity = new \App\Entity\File();
        $fileEntity->setPath($path);
        $this->getDoctrine()->getManager()->persist($fileEntity);
        $this->getDoctrine()->getManager()->flush();

        return $this->json($fileEntity, 200);
    }
}
