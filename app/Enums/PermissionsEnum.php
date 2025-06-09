<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    case VIEW_ANY_ORDERS = 'view any orders';

    case CREATE_CUSTOMERS = 'create customers';
    case EDIT_CUSTOMERS = 'edit customers';
    case DELETE_CUSTOMERS = 'delete customers';

    case VIEW_ANY_TEMPLATES = 'view any templates';
}
