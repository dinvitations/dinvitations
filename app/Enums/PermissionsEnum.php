<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    case MANAGE_ORDERS = 'manage orders';
    case VIEW_ANY_ORDERS = 'view any orders';

    case MANAGE_CUSTOMERS = 'manage customers';
    case CREATE_CUSTOMERS = 'create customers';
    case VIEW_CUSTOMERS = 'view customers';
    case EDIT_CUSTOMERS = 'edit customers';
    case DELETE_CUSTOMERS = 'delete customers';

    case MANAGE_TEMPLATES = 'manage templates';
    case VIEW_ANY_TEMPLATES = 'view any templates';
}
