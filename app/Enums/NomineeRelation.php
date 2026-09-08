<?php

namespace App\Enums;

enum NomineeRelation: string
{
    case SPOUSE = 'Spouse';
    case SON = 'Son';
    case UNMARRIED_DAUGHTER = 'Unmarried Daughter';
    case MARRIED_DAUGHTER = 'Married Daughter';
    case MOTHER = 'Mother';
    case FATHER = 'Father';
    case BROTHER = 'Brother';
    case SISTER = 'Sister';
    case SELF = 'Self';
    case OTHER = 'Other Legal Heir';
}
