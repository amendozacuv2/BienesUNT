@php
    $selectedValue = trim((string) ($currentValue ?? ''));
    $mode = $mode ?? 'remote';
    $placeholder = $placeholder ?? 'Seleccione o escriba un valor';
    $noApplyValue = 'NO APLICA';
    $isNoApplySelected = mb_strtoupper($selectedValue) === $noApplyValue;
@endphp

<div class="form-group">
    <label for="{{ $inputId }}">{{ $label }}</label>

    <div wire:ignore>
        <select
            id="{{ $inputId }}"
            class="form-control select2bs4 js-estate-select2"
            data-field="{{ $field }}"
            data-model="{{ $model }}"
            data-mode="{{ $mode }}"
            data-placeholder="{{ $placeholder }}"
            data-options-url="{{ route('estates.field-options') }}"
            style="width: 100%;"
        >
            <option value="" @selected($selectedValue === '')></option>
            <option value="{{ $noApplyValue }}" @selected($isNoApplySelected)>
                {{ $noApplyValue }}
            </option>

            @if ($selectedValue !== '' && ! $isNoApplySelected)
                <option value="{{ $selectedValue }}" selected>
                    {{ $selectedValue }}
                </option>
            @endif
        </select>
    </div>

    @error($errorKey)
        <small class="text-danger d-block mt-1">{{ $message }}</small>
    @enderror
</div>

@once
    @script
        <script>
            const initEstateSelect2Fields = () => {
                if (!window.jQuery || !jQuery.fn.select2) {
                    return;
                }

                jQuery('.js-estate-select2').each(function () {
                    const $select = jQuery(this);

                    if ($select.data('estate-select2-ready')) {
                        return;
                    }

                    $select.data('estate-select2-ready', true);

                    const normalizeText = (value) => {
                        return String(value || '')
                            .replace(/\s+/g, ' ')
                            .trim()
                            .toUpperCase();
                    };

                    const noApplyOption = {
                        id: 'NO APLICA',
                        text: 'NO APLICA'
                    };
                    const select2Options = {
                        theme: 'bootstrap4',
                        width: '100%',
                        tags: true,
                        allowClear: true,
                        placeholder: $select.data('placeholder') || 'Seleccione o escriba un valor',
                        createTag: function (params) {
                            const term = normalizeText(params.term);

                            if (term.length === 0) {
                                return null;
                            }

                            return {
                                id: term,
                                text: term,
                                newTag: true
                            };
                        },
                        templateResult: function (item) {
                            if (item.loading) {
                                return item.text;
                            }

                            const text = item.text || item.id || '';

                            if (item.newTag) {
                                return 'Usar nuevo: ' + text;
                            }

                            return text;
                        }
                    };

                    if ($select.data('mode') === 'remote') {
                        select2Options.minimumInputLength = 0;
                        select2Options.ajax = {
                            url: $select.data('options-url'),
                            dataType: 'json',
                            delay: 350,
                            data: function (params) {
                                return {
                                    field: $select.data('field'),
                                    term: String(params.term || '').trim()
                                };
                            },
                            transport: function (params, success, failure) {
                                const term = String(params.data.term || '').trim();

                                if (term.length < 3) {
                                    success({
                                        results: [noApplyOption]
                                    });

                                    return {
                                        abort: function () {}
                                    };
                                }

                                const request = jQuery.ajax(params);

                                request.then(success);
                                request.fail(failure);

                                return request;
                            },
                            processResults: function (data) {
                                const results = (data.results || [])
                                    .filter(function (item) {
                                        return normalizeText(item.id) !== noApplyOption.id;
                                    });

                                return {
                                    results: [noApplyOption].concat(results)
                                };
                            },
                            cache: true
                        };
                    }

                    $select.select2(select2Options);

                    $select.on('change', function () {
                        const model = $select.data('model');
                        const value = normalizeText($select.val());

                        $wire.set(model, value);
                    });
                });
            };

            initEstateSelect2Fields();

            Livewire.hook('morph.updated', () => {
                initEstateSelect2Fields();
            });

            document.addEventListener('livewire:navigated', () => {
                initEstateSelect2Fields();
            });
        </script>
    @endscript
@endonce
