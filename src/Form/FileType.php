<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\File;
use App\Repository\FileRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileType extends AbstractType
{
    private $fileRepository;

    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $multiple = $options['multiple'];

        $builder->addModelTransformer(new CallbackTransformer(function ($file) use ($multiple) {
            if (null === $file) {
                return null;
            }

            if ($multiple) {
                $array = [];
                foreach ($file as $item) {
                    $array[] = $item->getId();
                }

                return $array;
            }

            return $file->getId();
        }, function ($id) use ($multiple) {
            if (!$id) {
                return null;
            }

            if ($multiple) {
                $files = $this->fileRepository->findBy(['id' => $id]);

                if (!$files) {
                    throw new TransformationFailedException();
                }

                return $files;
            }

            $file = $this->fileRepository
                ->find($id);

            if (null === $file) {
                throw new TransformationFailedException();
            }

            return $file;
        }));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => File::class,
            'multiple' => false,
        ]);
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}
