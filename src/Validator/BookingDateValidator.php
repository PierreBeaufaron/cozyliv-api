<?php

namespace App\Validator;

use App\Repository\BookingRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class BookingDateValidator extends ConstraintValidator
{

    public function __construct(
        private BookingRepository $bookingRepository
    ) {}

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof BookingDate) {
            throw new \LogicException('Invalid constraint type.');
        }

        $room = $value->getRoom();
        $startDate = $value->getStartDate();
        $endDate = $value->getEndDate();

        if (!$room || !$startDate || !$endDate) {
            return;
        }

        // Check conflict with Repository method
        $conflictingBookings = $this->bookingRepository->findConflictingBookings($room, $startDate, $endDate);

        if (!empty($conflictingBookings)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
