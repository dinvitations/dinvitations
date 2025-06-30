<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    case MANAGE_ORDERS = 'manage orders';
    case VIEW_ANY_ORDERS = 'view any orders';
    case CREATE_ORDERS = 'create orders';
    case VIEW_ORDERS = 'view orders';
    case EDIT_ORDERS = 'edit orders';
    case DELETE_ORDERS = 'delete orders';
    case RESTORE_ORDERS = 'restore orders';

    case MANAGE_CUSTOMERS = 'manage customers';
    case CREATE_CUSTOMERS = 'create customers';
    case VIEW_CUSTOMERS = 'view customers';
    case EDIT_CUSTOMERS = 'edit customers';
    case DELETE_CUSTOMERS = 'delete customers';
    case RESTORE_CUSTOMERS = 'restore customers';

    case MANAGE_TEMPLATES = 'manage templates';
    case VIEW_ANY_TEMPLATES = 'view any templates';

    case MANAGE_GUESTS = 'manage guests';
    case VIEW_ANY_GUESTS = 'view any guests';
    case CREATE_GUESTS = 'create guests';
    case VIEW_GUESTS = 'view guests';
    case EDIT_GUESTS = 'edit guests';
    case DELETE_ANY_GUESTS = 'delete any guests';
    case DELETE_GUESTS = 'delete guests';
    case RESTORE_ANY_GUESTS = 'restore any guests';
    case RESTORE_GUESTS = 'restore guests';
}
