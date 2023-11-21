<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorErrorService
{
    private ValidatorInterface $validator;

    /**
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function getErrors($entity): array
    {
        $errors = $this->validator->validate($entity);
        $errorsMessages = [];
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $errorsMessages[] = $error->getMessage();
            }
        }
        return $errorsMessages;
    }
}