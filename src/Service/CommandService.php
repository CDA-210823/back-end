<?php

namespace App\Service;

class CommandService
{
    public function generateDefaultStatus(): string
    {
        return 'En attente';
    }

    public function getCurrentDate(): \DateTimeInterface
    {

        return new \DateTime();
    }
}