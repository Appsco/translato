<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UploadFilesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $allowedExtensions = ['yml', 'xliff'];
        $builder
            ->add('save', 'checkbox', [
                'required' => false,
                'data' => false,
            ])
            ->add('files', 'file', [
                'required' => false,
                'multiple' => true,
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize' => '2M',
                            ]),
                            new Callback([
                                'callback' => function (UploadedFile $uploadedFile, ExecutionContextInterface $context) use ($allowedExtensions) {
                                    if (false == in_array($uploadedFile->getClientOriginalExtension(), $allowedExtensions)) {
                                        $context->buildViolation(sprintf('Allowed files are: %s', implode(', ', $allowedExtensions)))
                                            ->atPath('files')
                                            ->addViolation();
                                    }
                                },
                            ]),
                        ]
                    ]),
                ]
            ])
        ;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'upload_files';
    }
}
