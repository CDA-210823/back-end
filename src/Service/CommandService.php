<?php

namespace App\Service;

use App\Entity\Command;

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

    public function manageCommand(Command $command): void
    {
        $command->setDate($this->getCurrentDate());
        $command->setStatus($this->generateDefaultStatus());
    }
}