<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Presentation\Http\Requests;

use App\Domains\Ordering\Domain\ValueObjects\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && (
            (bool) ($user->is_admin ?? false)
            || (method_exists($user, 'can') && ($user->can('manage-orders') || $user->can('orders.manage')))
        );
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(OrderStatus::values())],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
