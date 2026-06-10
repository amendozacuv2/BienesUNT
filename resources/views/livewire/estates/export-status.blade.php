@php
    $status = $exportStatus['status'] ?? null;
    $isActive = in_array($status, ['pending', 'processing'], true);
@endphp

<div @if ($isActive) wire:poll.5s @endif>
    @if ($status)
        <div class="alert {{ $status === 'completed' ? 'alert-success' : ($status === 'failed' ? 'alert-danger' : 'alert-info') }} py-2 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <span>
                    <i class="fas {{ $status === 'completed' ? 'fa-check-circle' : ($status === 'failed' ? 'fa-exclamation-circle' : 'fa-spinner fa-spin') }} mr-1"></i>
                    {{ $exportStatus['message'] ?? 'Consultando exportación...' }}

                    @if (! empty($exportStatus['size']))
                        <small>({{ number_format($exportStatus['size'] / 1048576, 2) }} MB)</small>
                    @endif
                </span>

                @if ($status === 'completed')
                    <a href="{{ route('estates.export.download') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-download mr-1"></i>
                        Descargar
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
