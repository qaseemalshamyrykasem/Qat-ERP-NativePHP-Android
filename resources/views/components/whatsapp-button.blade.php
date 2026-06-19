@props([
    'phone' => null,
    'message' => null,
    'entityType' => null,
    'entityId' => null,
    'endpoint' => null,
    'size' => 'sm',
    'variant' => 'button',
    'text' => 'واتساب',
    'class' => '',
])

@php
    if ($phone) {
        $waService = app(\App\Services\WhatsAppService::class);
        $waLink = $waService->generateLink($phone, $message ?? '');
    }
@endphp

@if($phone)
    <a href="{{ $waLink }}" target="_blank" rel="noopener"
       class="btn btn-{{ $size == 'sm' ? 'sm' : ($size == 'lg' ? 'lg' : '') }} btn-outline-success {{ $class }}"
       title="إرسال رسالة واتساب">
        <i class="bi bi-whatsapp"></i> {{ $text }}
    </a>
@elseif($entityType && $entityId)
    <button onclick="whatsappSend('{{ $entityType }}', {{ $entityId }}{{ $endpoint ? ", '" . $endpoint . "'" : '' }})"
            class="btn btn-{{ $size == 'sm' ? 'sm' : ($size == 'lg' ? 'lg' : '') }} btn-outline-success {{ $class }}"
            title="إرسال عبر واتساب">
        <i class="bi bi-whatsapp"></i> {{ $text }}
    </button>
@endif