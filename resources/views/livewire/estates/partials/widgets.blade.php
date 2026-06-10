@php
    $widgets = $widgets ?? [];

    $total = (int) ($widgets['total'] ?? 0);

    $enUso = (int) ($widgets['situations']['EN USO'] ?? 0);
    $desuso = (int) ($widgets['situations']['DESUSO'] ?? 0);

    $bueno = (int) ($widgets['conservation']['BUENO'] ?? 0);
    $regular = (int) ($widgets['conservation']['REGULAR'] ?? 0);
    $malo = (int) ($widgets['conservation']['MALO'] ?? 0);
@endphp

<div
    class="row"
    wire:loading.class="opacity-50"
    wire:target="search,areaId,locationId,situation,conservationStatus,perPage"
>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($total) }}</h3>
                <p>Total de bienes</p>
            </div>

            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($enUso) }}</h3>
                <p>Bienes en uso</p>
            </div>

            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ number_format($desuso) }}</h3>
                <p>Bienes en desuso</p>
            </div>

            <div class="icon">
                <i class="fas fa-ban"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ number_format($bueno) }}</h3>
                <p>Conservación buena</p>
            </div>

            <div class="icon">
                <i class="fas fa-thumbs-up"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($regular) }}</h3>
                <p>Conservación regular</p>
            </div>

            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($malo) }}</h3>
                <p>Conservación mala</p>
            </div>

            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>
</div>