@props([
    'id' => 'confirmModal',
    'title' => 'تأكيد',
    'message' => 'هل أنت متأكد؟',
    'confirmText' => 'تأكيد',
    'confirmClass' => 'btn-danger',
    'cancelText' => 'إلغاء',
    'icon' => 'exclamation-triangle',
])

<div class="modal fade" id="{{ $id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4">
            <div class="mb-3">
                <i class="bi bi-{{ $icon }} text-danger" style="font-size:3rem"></i>
            </div>
            <h5 class="mb-2">{{ $title }}</h5>
            <p class="text-muted mb-4">{{ $message }}</p>
            <div class="d-flex gap-2 justify-content-center">
                <button class="btn {{ $confirmClass }}" id="{{ $id }}Confirm">{{ $confirmText }}</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">{{ $cancelText }}</button>
            </div>
        </div>
    </div>
</div>