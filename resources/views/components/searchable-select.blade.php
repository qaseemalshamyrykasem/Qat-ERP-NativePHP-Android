@props([
    'name' => null,
    'id' => null,
    'label' => '',
    'api' => '',
    'searchField' => 'name',
    'displayField' => 'name',
    'subField' => null,
    'extraField' => null,
    'valueField' => 'id',
    'placeholder' => 'ابحث...',
    'allowCreate' => false,
    'createRoute' => null,
    'createLabel' => 'إضافة جديد',
    'required' => false,
    'selected' => null,
    'selectedText' => null,
    'minChars' => 0,
    'extraParams' => [],
    'disabled' => false,
])

@php
    $componentId = $id ?: $name . '_' . uniqid();
    $extraParamsStr = http_build_query($extraParams);
@endphp

<div class="searchable-select mb-3" id="ss-{{ $componentId }}">
    @if($label)
        <label class="form-label {{ $required ? 'required' : '' }}">{{ $label }}</label>
    @endif
    <input type="hidden" name="{{ $name }}" id="{{ $componentId }}" value="{{ $selected ?? '' }}" {{ $disabled ? 'disabled' : '' }}>
    <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" class="form-control selected-display" id="{{ $componentId }}_display"
               placeholder="{{ $placeholder }}"
               value="{{ $selectedText ?? '' }}"
               {{ $disabled ? 'disabled' : '' }}
               autocomplete="off">
    </div>
    <div class="dropdown-menu" id="{{ $componentId }}_dropdown" style="width:100%;display:none;"></div>
</div>

<script>
(function() {
    const id = '{{ $componentId }}';
    const hidden = document.getElementById(id);
    const display = document.getElementById(id + '_display');
    const dropdown = document.getElementById(id + '_dropdown');
    const api = '{{ $api }}';
    const searchField = '{{ $searchField }}';
    const displayField = '{{ $displayField }}';
    const subField = '{{ $subField }}';
    const extraField = '{{ $extraField }}';
    const valueField = '{{ $valueField }}';
    const minChars = {{ $minChars }};
    const extraParams = '{{ $extraParamsStr }}';
    let timer = null;
    let isOpen = false;

    if (!hidden || !display || !dropdown) return;

    display.addEventListener('focus', () => {
        if (minChars === 0) search('');
        else dropdown.style.display = 'block';
        isOpen = true;
    });

    display.addEventListener('input', () => {
        hidden.value = '';
        clearTimeout(timer);
        const q = display.value.trim();
        if (q.length < minChars && minChars > 0) return;
        timer = setTimeout(() => search(q), 300);
    });

    display.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { dropdown.style.display = 'none'; isOpen = false; display.blur(); }
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#ss-' + id)) { dropdown.style.display = 'none'; isOpen = false; }
    });

    async function search(query) {
        try {
            let url = api + '?per_page=10';
            if (query) url += '&search=' + encodeURIComponent(query);
            if (extraParams) url += '&' + extraParams;
            const res = await fetch(url, { headers: { Accept: 'application/json', credentials: 'same-origin' } });
            const json = await res.json();
            const items = json.data || [];
            let html = '';
            items.forEach(item => {
                const val = item[valueField];
                const txt = item[displayField] || '';
                const sub = subField && item[subField] ? item[subField] : '';
                const extra = extraField && item[extraField] ? item[extraField] : '';
                html += `<div class="dropdown-item" data-value="${val}" data-text="${txt}">
                    <div class="fw-semibold">${txt}</div>
                    ${sub ? `<div class="sub-info">${sub}</div>` : ''}
                    ${extra ? `<div class="extra-info text-muted">${extra}</div>` : ''}
                </div>`;
            });
            @if($allowCreate)
            html += `<div class="dropdown-item add-new-btn" onclick="{{ $createRoute ? "document.querySelector('#'+id+'_dropdown').style.display='none';window.location='" . $createRoute . "'" : '' }}">
                <i class="bi bi-plus-circle"></i> {{ $createLabel }}
            </div>`;
            @endif
            if (!items.length && !{{ $allowCreate ? 'true' : 'false' }}) {
                html = '<div class="text-center text-muted p-3 small">لا توجد نتائج</div>';
            }
            dropdown.innerHTML = html;
            dropdown.style.display = items.length ? 'block' : (html ? 'block' : 'none');

            dropdown.querySelectorAll('.dropdown-item:not(.add-new-btn)').forEach(item => {
                item.addEventListener('click', () => {
                    hidden.value = item.dataset.value;
                    display.value = item.dataset.text;
                    dropdown.style.display = 'none';
                    isOpen = false;
                });
            });
        } catch (e) { console.error('Search error:', e); }
    }
})();
</script>