<?php

namespace App\Enums;

/** Contact by intent (D-057, D-059): one number per purpose, shown only where the decisions allow. */
enum ContactKind: string
{
    case PhoneMain = 'phone_main';
    case Whatsapp = 'whatsapp';
    case ComplaintsFeedbackFranchise = 'complaints_feedback_franchise';
    case CateringB2bEvents = 'catering_b2b_events';
    case Email = 'email';
}
